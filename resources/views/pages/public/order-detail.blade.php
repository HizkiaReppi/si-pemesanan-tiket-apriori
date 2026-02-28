<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Events\PaymentVerified;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Detail Pesanan')] #[Layout('layouts.guest')] class extends Component {
    use WithFileUploads;

    public Order $order;

    public string $qrSvg = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $bankTransferProof;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items', 'payment', 'checkIns', 'voucherUsages.voucher']);

        if ($this->order->isPaid() && $this->order->qr_token) {
            $this->qrSvg = app(QrCodeService::class)->generateSvg($this->order->qr_token, 250);
        }
    }

    /**
     * Upload bukti transfer bank tanpa login.
     */
    public function uploadProof(): void
    {
        $key = 'upload-proof:' . $this->order->id . ':' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            session()->flash('upload-error', 'Terlalu banyak percobaan. Coba lagi nanti.');

            return;
        }

        RateLimiter::hit($key, 300);

        $this->validate([
            'bankTransferProof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ], [
            'bankTransferProof.required' => 'Silakan pilih file bukti transfer.',
            'bankTransferProof.mimes' => 'Format file harus JPG, PNG, WebP, atau PDF.',
            'bankTransferProof.max' => 'Ukuran file maksimal 2MB.',
        ]);

        if (! $this->order->payment || $this->order->payment_method !== PaymentMethod::BankTransfer) {
            session()->flash('upload-error', 'Pesanan ini tidak menggunakan metode transfer bank.');

            return;
        }

        if (! in_array($this->order->status, [OrderStatus::Pending, OrderStatus::AwaitingPayment])) {
            session()->flash('upload-error', 'Bukti transfer hanya dapat diunggah untuk pesanan yang belum dibayar.');

            return;
        }

        $path = $this->bankTransferProof->store('bank-transfer-proofs', 'public');

        $this->order->payment->update([
            'bank_transfer_proof' => $path,
            'status' => PaymentStatus::Verifying,
        ]);

        if ($this->order->status === OrderStatus::Pending) {
            $this->order->update(['status' => OrderStatus::AwaitingPayment]);
        }

        $this->order->refresh();
        $this->bankTransferProof = null;

        session()->flash('upload-success', 'Bukti transfer berhasil diunggah. Menunggu verifikasi admin.');
    }

    /**
     * Cek status pembayaran Midtrans dan update jika sudah berhasil.
     */
    public function checkMidtransStatus(): void
    {
        if ($this->order->payment_method !== PaymentMethod::Midtrans) {
            return;
        }

        if (! $this->order->payment || $this->order->payment->isPaid()) {
            $this->order->refresh();

            return;
        }

        try {
            $midtrans = app(MidtransService::class);
            $status = $midtrans->getTransactionStatus($this->order->code);

            if ($midtrans->isTransactionSuccess($status)) {
                $this->order->payment->update([
                    'status' => PaymentStatus::Paid,
                    'midtrans_transaction_id' => $status['transaction_id'] ?? null,
                    'paid_at' => now(),
                    'metadata' => $status,
                ]);

                $this->order->update(['status' => OrderStatus::Paid]);

                OrderPaid::dispatch($this->order);
            } elseif ($midtrans->isTransactionFailed($status)) {
                $this->order->payment->update([
                    'status' => PaymentStatus::Failed,
                    'midtrans_transaction_id' => $status['transaction_id'] ?? null,
                    'metadata' => $status,
                ]);

                if (in_array($this->order->status, [OrderStatus::Pending, OrderStatus::AwaitingPayment])) {
                    $this->order->update(['status' => OrderStatus::Expired]);
                }
            } elseif ($midtrans->isTransactionPending($status)) {
                $this->order->payment->update([
                    'midtrans_transaction_id' => $status['transaction_id'] ?? null,
                    'metadata' => $status,
                ]);

                if ($this->order->status === OrderStatus::Pending) {
                    $this->order->update(['status' => OrderStatus::AwaitingPayment]);
                }
            }
        } catch (\Exception $e) {
            // Silently handle - webhook will eventually update
        }

        $this->order->refresh();

        if ($this->order->isPaid() && $this->order->qr_token) {
            $this->qrSvg = app(QrCodeService::class)->generateSvg($this->order->qr_token, 250);
        }
    }

    /**
     * @return array<int, array{status: OrderStatus, label: string, reached: bool}>
     */
    public function getTimelineSteps(): array
    {
        $allSteps = [
            OrderStatus::Pending,
            OrderStatus::AwaitingPayment,
            OrderStatus::Paid,
            OrderStatus::Confirmed,
            OrderStatus::Completed,
        ];

        $currentIndex = array_search($this->order->status, $allSteps);
        $isCancelledOrExpired = in_array($this->order->status, [OrderStatus::Cancelled, OrderStatus::Expired]);

        return collect($allSteps)->map(function (OrderStatus $step, int $index) use ($currentIndex, $isCancelledOrExpired) {
            return [
                'status' => $step,
                'label' => $step->label(),
                'reached' => ! $isCancelledOrExpired && $currentIndex !== false && $index <= $currentIndex,
            ];
        })->toArray();
    }
}; ?>

<div class="relative">
    <!-- Decorative gradient header -->
    <div class="absolute inset-x-0 top-0 h-56 overflow-hidden bg-linear-to-br from-sky-600 via-sky-700 to-sky-900 dark:from-sky-800 dark:via-sky-900 dark:to-zinc-900">
        <div class="animate-float-gentle absolute -top-10 left-1/4 size-36 rounded-full bg-white/10 blur-2xl"></div>
        <div class="animate-float-slow absolute bottom-0 right-1/3 size-44 rounded-full bg-sky-400/10 blur-2xl"></div>
        <div class="animate-float-reverse absolute right-1/4 top-1/3 size-28 rounded-full bg-cyan-200/5 blur-2xl"></div>
        <div class="absolute -bottom-1 left-0 w-full">
            <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0,40 C360,100 1080,0 1440,60 L1440,100 L0,100Z" class="fill-zinc-50 dark:fill-zinc-900" />
            </svg>
        </div>
    </div>

    <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-sky-100 backdrop-blur-sm">
                    <flux:icon name="clipboard-document-list" variant="mini" class="size-4" />
                    Detail Pesanan
                </span>
                <h1 class="mt-3 text-3xl font-bold text-white">Detail Pesanan</h1>
                <p class="mt-1 font-mono text-lg text-sky-200">{{ $order->code }}</p>
            </div>
            @php
                $statusColors = [
                    'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                    'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                    'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                    'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                    'gray' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                ];
                $badgeClass = $statusColors[$order->status->color()] ?? $statusColors['gray'];
            @endphp
            <span class="inline-flex w-fit items-center rounded-full px-4 py-2 text-sm font-semibold {{ $badgeClass }}">
                {{ $order->status->label() }}
            </span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Status Timeline -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-white">Status Pesanan</h2>

                @if(in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Expired]))
                    <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                        <div class="flex items-center gap-3">
                            <flux:icon name="x-circle" class="size-6 text-red-500" />
                            <div>
                                <p class="font-medium text-red-800 dark:text-red-400">
                                    Pesanan {{ $order->status->label() }}
                                </p>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400/80">
                                    @if($order->status === OrderStatus::Expired)
                                        Pesanan ini telah melewati batas waktu pembayaran.
                                    @else
                                        Pesanan ini telah dibatalkan.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Mobile: vertical timeline --}}
                    <div class="space-y-4 sm:hidden">
                        @foreach($this->getTimelineSteps() as $index => $step)
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $step['reached'] ? 'bg-sky-500 text-white' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500' }}">
                                        @if($step['reached'])
                                            <flux:icon name="check" variant="mini" class="size-4" />
                                        @else
                                            <span class="text-xs font-medium">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    @if($index < count($this->getTimelineSteps()) - 1)
                                        <div class="mt-1 h-6 w-0.5 {{ $step['reached'] && ($this->getTimelineSteps()[$index + 1]['reached'] ?? false) ? 'bg-sky-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                                    @endif
                                </div>
                                <span class="pt-1 text-sm font-medium {{ $step['reached'] ? 'text-sky-600 dark:text-sky-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Desktop: horizontal timeline --}}
                    <div class="hidden items-center gap-1 sm:flex">
                        @foreach($this->getTimelineSteps() as $index => $step)
                            <div class="flex flex-1 flex-col items-center text-center">
                                <div class="flex w-full items-center">
                                    @if($index > 0)
                                        <div class="h-0.5 flex-1 {{ $step['reached'] ? 'bg-sky-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                                    @endif
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $step['reached'] ? 'bg-sky-500 text-white' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500' }}">
                                        @if($step['reached'])
                                            <flux:icon name="check" variant="mini" class="size-4" />
                                        @else
                                            <span class="text-xs font-medium">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    @if($index < count($this->getTimelineSteps()) - 1)
                                        <div class="h-0.5 flex-1 {{ $step['reached'] && ($this->getTimelineSteps()[$index + 1]['reached'] ?? false) ? 'bg-sky-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                                    @endif
                                </div>
                                <span class="mt-2 text-xs font-medium {{ $step['reached'] ? 'text-sky-600 dark:text-sky-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Expiration Warning -->
            @if($order->status === OrderStatus::Pending && $order->expires_at && $order->expires_at->isFuture())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <div class="flex items-start gap-3">
                        <flux:icon name="clock" class="mt-0.5 size-5 text-amber-600 dark:text-amber-400" />
                        <div>
                            <p class="font-medium text-amber-800 dark:text-amber-400">Batas Waktu Pembayaran</p>
                            <p class="mt-1 text-sm text-amber-700 dark:text-amber-400/80">
                                Selesaikan pembayaran sebelum
                                <strong>{{ $order->expires_at->translatedFormat('d F Y, H:i') }} WIT</strong>.
                                Pesanan akan otomatis dibatalkan jika melewati batas waktu.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Midtrans Payment -->
            @if($order->payment_method === PaymentMethod::Midtrans && in_array($order->status, [OrderStatus::Pending, OrderStatus::AwaitingPayment]) && $order->payment?->midtrans_snap_token)
                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-800 dark:bg-sky-900/20">
                    <div class="mb-4 flex items-center gap-3">
                        <flux:icon name="credit-card" class="size-6 text-sky-600 dark:text-sky-400" />
                        <h2 class="font-semibold text-sky-900 dark:text-sky-300">Pembayaran Online</h2>
                    </div>
                    <p class="mb-4 text-sm text-sky-800 dark:text-sky-300">
                        Klik tombol di bawah untuk menyelesaikan pembayaran melalui QRIS, Virtual Account, e-Wallet, atau metode lainnya.
                    </p>
                    <button
                        id="pay-button"
                        type="button"
                        class="w-full rounded-lg bg-sky-600 px-4 py-3 font-semibold text-white transition hover:bg-sky-700"
                    >
                        Bayar Sekarang - Rp {{ number_format($order->total, 0, ',', '.') }}
                    </button>
                </div>

                @php
                    $snapJsUrl = app(\App\Services\MidtransService::class)->getSnapJsUrl();
                    $clientKey = app(\App\Services\MidtransService::class)->getClientKey();
                    $snapToken = $order->payment->midtrans_snap_token;
                @endphp
                <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
                <script>
                    document.getElementById('pay-button').addEventListener('click', function() {
                        window.snap.pay('{{ $snapToken }}', {
                            onSuccess: function(result) {
                                @this.call('checkMidtransStatus');
                            },
                            onPending: function(result) {
                                @this.call('checkMidtransStatus');
                            },
                            onError: function(result) {
                                @this.call('checkMidtransStatus');
                            },
                            onClose: function() {
                                @this.call('checkMidtransStatus');
                            }
                        });
                    });
                </script>
            @endif

            <!-- Bank Transfer Instructions -->
            @if($order->payment_method === PaymentMethod::BankTransfer && in_array($order->status, [OrderStatus::Pending, OrderStatus::AwaitingPayment]))
                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-800 dark:bg-sky-900/20">
                    <div class="mb-4 flex items-center gap-3">
                        <flux:icon name="building-library" class="size-6 text-sky-600 dark:text-sky-400" />
                        <h2 class="font-semibold text-sky-900 dark:text-sky-300">Instruksi Transfer Bank</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-lg bg-white p-4 dark:bg-zinc-800">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Bank Tujuan</p>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white">Bank BRI</p>
                            <p class="mt-1 font-mono text-lg font-semibold text-zinc-900 dark:text-white">0123-4567-8901-23</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">a.n. Kolam Po'opo</p>
                        </div>

                        <div class="rounded-lg bg-white p-4 dark:bg-zinc-800">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Jumlah Transfer</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="space-y-2 text-sm text-sky-800 dark:text-sky-300">
                            <p class="font-medium">Petunjuk:</p>
                            <ol class="list-inside list-decimal space-y-1">
                                <li>Transfer sesuai jumlah di atas ke rekening tujuan</li>
                                <li>Simpan bukti transfer Anda</li>
                                <li>Upload bukti transfer di bawah ini</li>
                                <li>Tunggu verifikasi dari admin (maks. 1×24 jam)</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Upload Bukti Transfer -->
                @if(! $order->payment?->bank_transfer_proof)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-800 dark:bg-emerald-900/20">
                        <div class="mb-4 flex items-center gap-3">
                            <flux:icon name="arrow-up-tray" class="size-6 text-emerald-600 dark:text-emerald-400" />
                            <h2 class="font-semibold text-emerald-900 dark:text-emerald-300">Upload Bukti Transfer</h2>
                        </div>

                        @if(session('upload-success'))
                            <div class="mb-4 rounded-lg bg-green-100 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                {{ session('upload-success') }}
                            </div>
                        @endif

                        @if(session('upload-error'))
                            <div class="mb-4 rounded-lg bg-red-100 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                {{ session('upload-error') }}
                            </div>
                        @endif

                        <form wire:submit="uploadProof">
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-emerald-800 dark:text-emerald-300">
                                        File Bukti Transfer (JPG, PNG, WebP, PDF - Maks. 2MB)
                                    </label>
                                    <input
                                        type="file"
                                        wire:model="bankTransferProof"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                                        class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm text-zinc-700 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700 dark:border-emerald-700 dark:bg-zinc-800 dark:text-zinc-300"
                                    />
                                    @error('bankTransferProof')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if($bankTransferProof)
                                    <div class="rounded-lg bg-white p-3 dark:bg-zinc-800">
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            File: <span class="font-medium">{{ $bankTransferProof->getClientOriginalName() }}</span>
                                            ({{ number_format($bankTransferProof->getSize() / 1024, 1) }} KB)
                                        </p>
                                    </div>
                                @endif

                                <flux:button type="submit" variant="primary" class="w-full" icon="arrow-up-tray"
                                    wire:loading.attr="disabled" wire:target="uploadProof,bankTransferProof">
                                    <span wire:loading.remove wire:target="uploadProof">Upload Bukti Transfer</span>
                                    <span wire:loading wire:target="uploadProof">Mengunggah...</span>
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <div class="flex items-center gap-3">
                            <flux:icon name="check-circle" class="size-6 text-blue-500" />
                            <div>
                                <p class="font-medium text-blue-800 dark:text-blue-400">Bukti Transfer Sudah Diunggah</p>
                                <p class="text-sm text-blue-600 dark:text-blue-400/80">
                                    Menunggu verifikasi dari admin. Proses verifikasi maks. 1×24 jam.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Order Items -->
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-6 py-4 dark:border-zinc-800">
                    <h2 class="font-semibold text-zinc-900 dark:text-white">Item Pesanan</h2>
                </div>
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4 last:border-b-0 dark:border-zinc-800">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $item->item_name }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $item->quantity }} × Rp {{ number_format($item->item_price, 0, ',', '.') }}
                            </p>
                        </div>
                        <p class="font-semibold text-zinc-900 dark:text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach

                <!-- Totals -->
                <div class="space-y-2 px-6 py-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                        <span class="text-zinc-900 dark:text-white">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600 dark:text-green-400">Diskon
                                @if($order->voucherUsages->isNotEmpty())
                                    ({{ $order->voucherUsages->first()->voucher->code }})
                                @endif
                            </span>
                            <span class="text-green-600 dark:text-green-400">
                                -Rp {{ number_format($order->discount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        <span class="text-lg font-semibold text-zinc-900 dark:text-white">Total</span>
                        <span class="text-lg font-bold text-zinc-900 dark:text-white">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-white">Informasi Pembayaran</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Metode Pembayaran</p>
                        <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $order->payment_method->label() }}</p>
                    </div>
                    @if($order->payment)
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Status Pembayaran</p>
                            @php
                                $paymentBadgeClass = $statusColors[$order->payment->status->color()] ?? $statusColors['gray'];
                            @endphp
                            <span class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $paymentBadgeClass }}">
                                {{ $order->payment->status->label() }}
                            </span>
                        </div>
                        @if($order->payment->paid_at)
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Tanggal Pembayaran</p>
                                <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                                    {{ $order->payment->paid_at->translatedFormat('d F Y, H:i') }}
                                </p>
                            </div>
                        @endif
                    @endif
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Tanggal Pesanan</p>
                        <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $order->created_at->translatedFormat('d F Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- QR Code -->
            @if($order->isPaid() && $qrSvg)
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 font-semibold text-zinc-900 dark:text-white">E-Tiket QR Code</h2>
                    <div class="mx-auto flex w-fit items-center justify-center rounded-xl bg-white p-3">
                        {!! $qrSvg !!}
                    </div>
                    <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                        Tunjukkan QR code ini di pintu masuk pada tanggal kunjungan Anda.
                    </p>
                </div>
            @endif

            <!-- Visit Info -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-white">Info Kunjungan</h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 text-zinc-400" />
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tanggal</p>
                            <p class="font-medium text-zinc-900 dark:text-white">
                                {{ $order->visit_date->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:icon name="tag" variant="mini" class="size-5 text-zinc-400" />
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tipe Hari</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $order->day_type->label() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:icon name="user" variant="mini" class="size-5 text-zinc-400" />
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Nama</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $order->customer_name }}</p>
                        </div>
                    </div>
                    @if($order->customer_email)
                        <div class="flex items-center gap-3">
                            <flux:icon name="envelope" variant="mini" class="size-5 text-zinc-400" />
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Email</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $order->customer_email }}</p>
                            </div>
                        </div>
                    @endif
                    @if($order->customer_phone)
                        <div class="flex items-center gap-3">
                            <flux:icon name="phone" variant="mini" class="size-5 text-zinc-400" />
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Telepon</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $order->customer_phone }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Check-in Status -->
            @if($order->isPaid())
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 font-semibold text-zinc-900 dark:text-white">Status Check-in</h2>
                    @if($order->checkIns->isNotEmpty())
                        @php $latestCheckIn = $order->checkIns->sortByDesc('scanned_at')->first(); @endphp
                        <div class="flex items-center gap-3 rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                            <flux:icon name="check-circle" class="size-6 text-green-500" />
                            <div>
                                <p class="font-medium text-green-800 dark:text-green-400">Sudah Check-in</p>
                                <p class="text-sm text-green-600 dark:text-green-400/80">
                                    {{ $latestCheckIn->scanned_at->translatedFormat('d F Y, H:i') }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                            <flux:icon name="clock" class="size-6 text-zinc-400" />
                            <div>
                                <p class="font-medium text-zinc-700 dark:text-zinc-300">Belum Check-in</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Scan QR di pintu masuk pada tanggal kunjungan.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Actions -->
            <div class="space-y-3">
                <flux:button :href="route('tiket.index')" variant="ghost" wire:navigate class="w-full" icon="ticket">
                    Beli Tiket Lain
                </flux:button>
                @auth
                    <flux:button :href="route('pesanan-saya')" variant="primary" wire:navigate class="w-full" icon="clipboard-document-list">
                        Pesanan Saya
                    </flux:button>
                @endauth
            </div>
        </div>
    </div>
</div>
</div>

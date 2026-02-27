<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Services\QrCodeService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detail Pesanan')] #[Layout('layouts.guest')] class extends Component {
    public Order $order;

    public string $qrSvg = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items', 'payment', 'checkIns', 'voucherUsages.voucher']);

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

<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Detail Pesanan</h1>
                <p class="mt-1 font-mono text-lg text-zinc-500 dark:text-zinc-400">{{ $order->code }}</p>
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
                    <div class="flex items-center gap-1">
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
                                <li>Upload bukti transfer melalui halaman pesanan Anda</li>
                                <li>Tunggu verifikasi dari admin (maks. 1×24 jam)</li>
                            </ol>
                        </div>
                    </div>
                </div>
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

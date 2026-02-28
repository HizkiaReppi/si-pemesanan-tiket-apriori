<?php

use App\Actions\VerifyBankTransfer;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Events\PaymentVerified;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Verifikasi Pembayaran')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $filterMethod = '';

    public string $filterStatus = '';

    public string $search = '';

    public ?int $editingOrderId = null;

    public string $newOrderStatus = '';

    public function updatedFilterMethod(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function approve(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        app(VerifyBankTransfer::class)->execute($payment, auth()->id(), approved: true);
        session()->flash('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        app(VerifyBankTransfer::class)->execute($payment, auth()->id(), approved: false);
        session()->flash('success', 'Pembayaran ditolak.');
    }

    /**
     * Verifikasi manual: tandai pembayaran sebagai lunas (untuk semua metode).
     */
    public function markAsPaid(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);

        if ($payment->isPaid()) {
            session()->flash('error', 'Pembayaran sudah berstatus lunas.');

            return;
        }

        DB::transaction(function () use ($payment): void {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'paid_at' => now(),
            ]);

            $payment->order->update(['status' => OrderStatus::Paid]);

            PaymentVerified::dispatch($payment);
            OrderPaid::dispatch($payment->order);
        });

        session()->flash('success', 'Pembayaran berhasil ditandai lunas.');
    }

    /**
     * Mulai mengubah status pesanan.
     */
    public function startEditOrderStatus(int $orderId, string $currentStatus): void
    {
        $this->editingOrderId = $orderId;
        $this->newOrderStatus = $currentStatus;
    }

    /**
     * Simpan perubahan status pesanan.
     */
    public function updateOrderStatus(): void
    {
        if (! $this->editingOrderId || ! $this->newOrderStatus) {
            return;
        }

        $order = Order::findOrFail($this->editingOrderId);
        $newStatus = OrderStatus::from($this->newOrderStatus);

        $order->update(['status' => $newStatus]);

        if ($newStatus === OrderStatus::Paid && $order->payment && ! $order->payment->isPaid()) {
            $order->payment->update([
                'status' => PaymentStatus::Paid,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'paid_at' => now(),
            ]);
        }

        $this->editingOrderId = null;
        $this->newOrderStatus = '';

        session()->flash('success', "Status pesanan {$order->code} diperbarui menjadi {$newStatus->label()}.");
    }

    public function cancelEditOrderStatus(): void
    {
        $this->editingOrderId = null;
        $this->newOrderStatus = '';
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $query = Payment::with('order')
            ->latest();

        if ($this->filterMethod) {
            $query->where('method', $this->filterMethod);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        } else {
            $query->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Verifying]);
        }

        if ($this->search) {
            $query->whereHas('order', function ($q): void {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('customer_email', 'like', "%{$this->search}%");
            });
        }

        $view->with('payments', $query->paginate(15));
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="xl">Verifikasi Pembayaran</flux:heading>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="grid gap-4 sm:grid-cols-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode pesanan, nama, email..." icon="magnifying-glass" />
            <flux:select wire:model.live="filterMethod">
                <flux:select.option value="">Semua Metode</flux:select.option>
                @foreach(PaymentMethod::cases() as $method)
                    <flux:select.option value="{{ $method->value }}">{{ $method->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterStatus">
                <flux:select.option value="">Menunggu Verifikasi</flux:select.option>
                @foreach(PaymentStatus::cases() as $status)
                    <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="space-y-4">
            @forelse($payments as $payment)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-3">
                                <p class="font-semibold text-zinc-900 dark:text-white">{{ $payment->order->code }}</p>
                                @php
                                    $methodColors = [
                                        'midtrans' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                        'bank_transfer' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400',
                                        'cash' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $methodColors[$payment->method->value] ?? 'bg-zinc-100 text-zinc-700' }}">
                                    {{ $payment->method->label() }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $payment->order->customer_name }}
                                @if($payment->order->customer_email) · {{ $payment->order->customer_email }} @endif
                                @if($payment->order->customer_phone) · {{ $payment->order->customer_phone }} @endif
                            </p>
                            <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                                @php
                                    $statusColors = [
                                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        'gray' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
                                        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                    ];
                                    $paymentBadge = $statusColors[$payment->status->color()] ?? $statusColors['gray'];
                                    $orderBadge = $statusColors[$payment->order->status->color()] ?? $statusColors['gray'];
                                @endphp
                                <span>Pembayaran: <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $paymentBadge }}">{{ $payment->status->label() }}</span></span>
                                <span>Pesanan:
                                    @if($editingOrderId === $payment->order->id)
                                        <select wire:model="newOrderStatus" class="rounded-md border border-zinc-300 px-2 py-1 text-xs dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                            @foreach(OrderStatus::cases() as $os)
                                                <option value="{{ $os->value }}">{{ $os->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button wire:click="updateOrderStatus" class="ml-1 rounded bg-sky-600 px-2 py-1 text-xs text-white hover:bg-sky-700">Simpan</button>
                                        <button wire:click="cancelEditOrderStatus" class="ml-1 rounded bg-zinc-300 px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-400 dark:bg-zinc-600 dark:text-zinc-200">Batal</button>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $orderBadge }}">{{ $payment->order->status->label() }}</span>
                                        <button wire:click="startEditOrderStatus({{ $payment->order->id }}, '{{ $payment->order->status->value }}')" class="ml-1 text-xs text-sky-600 underline hover:text-sky-800 dark:text-sky-400" title="Ubah status pesanan">
                                            <flux:icon name="pencil-square" variant="mini" class="inline size-3.5" />
                                        </button>
                                    @endif
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-zinc-400">{{ $payment->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if($payment->bank_transfer_proof)
                                <flux:button href="{{ Storage::url($payment->bank_transfer_proof) }}" target="_blank" variant="ghost" size="sm" icon="photo">
                                    Bukti
                                </flux:button>
                            @endif
                            @if(! $payment->isPaid())
                                @if($payment->status === PaymentStatus::Verifying)
                                    <flux:button x-on:click="$flux.modal('confirm-approve-{{ $payment->id }}').show()" variant="primary" size="sm" icon="check">
                                        Terima
                                    </flux:button>
                                    <flux:button x-on:click="$flux.modal('confirm-reject-{{ $payment->id }}').show()" variant="danger" size="sm" icon="x-mark">
                                        Tolak
                                    </flux:button>
                                @else
                                    <flux:button x-on:click="$flux.modal('confirm-paid-{{ $payment->id }}').show()" variant="primary" size="sm" icon="check-circle">
                                        Tandai Lunas
                                    </flux:button>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Confirmation Modals --}}
                    @if(! $payment->isPaid())
                        @if($payment->status === PaymentStatus::Verifying)
                            <flux:modal name="confirm-approve-{{ $payment->id }}" class="max-w-md">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                            <flux:icon name="check-circle" class="size-6 text-green-600" />
                                        </div>
                                        <flux:heading size="lg">Verifikasi Pembayaran</flux:heading>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Apakah Anda yakin ingin <strong>menerima</strong> pembayaran <strong>{{ $payment->order->code }}</strong> sebesar <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>?
                                    </p>
                                    <div class="flex justify-end gap-2">
                                        <flux:button x-on:click="$flux.modal('confirm-approve-{{ $payment->id }}').close()" variant="ghost">Batal</flux:button>
                                        <flux:button wire:click="approve({{ $payment->id }})" x-on:click="$flux.modal('confirm-approve-{{ $payment->id }}').close()" variant="primary" icon="check">Terima</flux:button>
                                    </div>
                                </div>
                            </flux:modal>

                            <flux:modal name="confirm-reject-{{ $payment->id }}" class="max-w-md">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                            <flux:icon name="x-circle" class="size-6 text-red-600" />
                                        </div>
                                        <flux:heading size="lg">Tolak Pembayaran</flux:heading>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Apakah Anda yakin ingin <strong>menolak</strong> pembayaran <strong>{{ $payment->order->code }}</strong>?
                                    </p>
                                    <div class="flex justify-end gap-2">
                                        <flux:button x-on:click="$flux.modal('confirm-reject-{{ $payment->id }}').close()" variant="ghost">Batal</flux:button>
                                        <flux:button wire:click="reject({{ $payment->id }})" x-on:click="$flux.modal('confirm-reject-{{ $payment->id }}').close()" variant="danger" icon="x-mark">Tolak</flux:button>
                                    </div>
                                </div>
                            </flux:modal>
                        @else
                            <flux:modal name="confirm-paid-{{ $payment->id }}" class="max-w-md">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-900/30">
                                            <flux:icon name="check-circle" class="size-6 text-sky-600" />
                                        </div>
                                        <flux:heading size="lg">Tandai Lunas</flux:heading>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Apakah Anda yakin ingin menandai pembayaran <strong>{{ $payment->order->code }}</strong> sebesar <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong> sebagai <strong>lunas</strong>?
                                    </p>
                                    <div class="flex justify-end gap-2">
                                        <flux:button x-on:click="$flux.modal('confirm-paid-{{ $payment->id }}').close()" variant="ghost">Batal</flux:button>
                                        <flux:button wire:click="markAsPaid({{ $payment->id }})" x-on:click="$flux.modal('confirm-paid-{{ $payment->id }}').close()" variant="primary" icon="check-circle">Tandai Lunas</flux:button>
                                    </div>
                                </div>
                            </flux:modal>
                        @endif
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="check-circle" class="mx-auto size-12 text-green-500" />
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Semua Terverifikasi</h3>
                    <p class="mt-2 text-zinc-600 dark:text-zinc-400">Tidak ada pembayaran yang menunggu verifikasi.</p>
                </div>
            @endforelse

            {{ $payments->links() }}
        </div>
    </div>
</div>

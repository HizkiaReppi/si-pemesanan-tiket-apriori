<?php

use App\Actions\CancelOrder;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detail Pesanan - Admin')] #[Layout('layouts.app')] class extends Component {
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items', 'payment', 'checkIns.scannedBy', 'voucherUsages.voucher', 'user']);
    }

    public function cancelOrder(): void
    {
        app(CancelOrder::class)->execute($this->order, auth()->id(), 'Dibatalkan oleh admin');

        session()->flash('success', 'Pesanan berhasil dibatalkan.');
        $this->order = $this->order->fresh(['items', 'payment', 'checkIns', 'voucherUsages.voucher', 'user']);
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ $order->code }}</flux:heading>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Dibuat: {{ $order->created_at->translatedFormat('d F Y H:i') }}</p>
            </div>
            <span class="text-lg font-semibold" style="color: {{ $order->status->color() }}">
                {{ $order->status->label() }}
            </span>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Customer Info -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-3 font-semibold text-zinc-900 dark:text-white">Informasi Pelanggan</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Nama</span>
                        <span class="text-zinc-900 dark:text-white">{{ $order->customer_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Email</span>
                        <span class="text-zinc-900 dark:text-white">{{ $order->customer_email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Telepon</span>
                        <span class="text-zinc-900 dark:text-white">{{ $order->customer_phone }}</span>
                    </div>
                    @if($order->user)
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Akun</span>
                            <span class="text-zinc-900 dark:text-white">{{ $order->user->email }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Info -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-3 font-semibold text-zinc-900 dark:text-white">Detail Pesanan</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Tanggal Kunjungan</span>
                        <span class="text-zinc-900 dark:text-white">{{ $order->visit_date->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Tipe Hari</span>
                        <span class="text-zinc-900 dark:text-white">{{ $order->day_type->label() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Metode Bayar</span>
                        <span class="text-zinc-900 dark:text-white">{{ $order->payment_method->label() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Item Pesanan</h3>
            </div>
            @foreach($order->items as $item)
                <div class="flex justify-between border-b border-zinc-100 p-4 last:border-b-0 dark:border-zinc-800">
                    <span class="text-zinc-900 dark:text-white">{{ $item->item_name }} × {{ $item->quantity }}</span>
                    <span class="font-medium text-zinc-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="space-y-1 p-4">
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-500">Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($order->discount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Diskon</span>
                        <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t pt-2 text-lg font-bold">
                    <span>Total</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Check-ins -->
        @if($order->checkIns->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Riwayat Check-in</h3>
                </div>
                @foreach($order->checkIns as $checkIn)
                    <div class="flex justify-between border-b border-zinc-100 p-4 last:border-b-0 dark:border-zinc-800">
                        <div>
                            <span style="color: {{ $checkIn->status->color() }}">{{ $checkIn->status->label() }}</span>
                            @if($checkIn->scannedBy)
                                <span class="text-sm text-zinc-500"> oleh {{ $checkIn->scannedBy->name }}</span>
                            @endif
                        </div>
                        <span class="text-sm text-zinc-500">{{ $checkIn->scanned_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Actions -->
        @if(! in_array($order->status->value, ['completed', 'cancelled']))
            <div class="flex gap-3">
                <flux:button wire:click="cancelOrder" wire:confirm="Yakin ingin membatalkan pesanan ini?" variant="danger">
                    Batalkan Pesanan
                </flux:button>
            </div>
        @endif
    </div>
</div>

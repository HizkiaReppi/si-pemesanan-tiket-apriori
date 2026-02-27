<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Pesanan Kasir')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $orders = Order::query()
            ->when($this->search, fn ($q) => $q->where('code', 'like', "%{$this->search}%")
                ->orWhere('customer_name', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        $view->with('orders', $orders);
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Pesanan</flux:heading>
            <flux:button href="{{ route('kasir.penjualan') }}" variant="primary" icon="plus">Pesanan Baru</flux:button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode atau nama..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="status">
                <option value="">Semua Status</option>
                @foreach(\App\Enums\OrderStatus::cases() as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </flux:select>
        </div>

        <!-- Table -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Kode</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Pelanggan</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Tanggal Kunjungan</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Metode</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Total</th>
                            <th class="px-4 py-3 text-center font-medium text-zinc-600 dark:text-zinc-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($orders as $order)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-zinc-900 dark:text-white">{{ $order->code }}</td>
                                <td class="px-4 py-3 text-zinc-900 dark:text-white">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $order->visit_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $order->payment_method?->label() ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-medium text-zinc-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <flux:badge size="sm" :color="$order->status->color()">{{ $order->status->label() }}</flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-zinc-400">Tidak ada pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $orders->links() }}</div>
    </div>
</div>

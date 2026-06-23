<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Kelola Pesanan')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $dateFilter = '';

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
        $query = Order::with('user')->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('customer_email', 'like', "%{$this->search}%");
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->dateFilter) {
            $query->whereDate('visit_date', $this->dateFilter);
        }

        $view->with('orders', $query->paginate(15));
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Kelola Pesanan</flux:heading>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode/nama/email..." icon="magnifying-glass" class="max-w-xs" />
            <flux:select wire:model.live="status" class="max-w-50">
                <flux:select.option value="">Semua Status</flux:select.option>
                @foreach(\App\Enums\OrderStatus::cases() as $s)
                    <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input wire:model.live="dateFilter" type="date" class="max-w-50" />
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Kode</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Tgl Kunjungan</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Total</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $order->code }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $order->customer_name }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $order->visit_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-zinc-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium" style="color: {{ $order->status->color() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <flux:button :href="route('admin.pesanan.detail', $order)" variant="ghost" size="xs" wire:navigate>
                                    Detail
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                Tidak ada pesanan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</div>

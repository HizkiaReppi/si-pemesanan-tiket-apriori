<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Pesanan Saya')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $query = Order::where('user_id', Auth::id())->latest();

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $view->with('orders', $query->paginate(10));
    }
}; ?>

<div>
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">Pesanan Saya</flux:heading>
            <flux:button :href="route('tiket.index')" variant="primary" icon="plus" wire:navigate size="sm">
                Beli Tiket
            </flux:button>
        </div>

        <!-- Filter -->
        <div class="mb-4">
            <flux:select wire:model.live="status" placeholder="Semua Status" class="max-w-xs">
                <flux:select.option value="">Semua Status</flux:select.option>
                @foreach(\App\Enums\OrderStatus::cases() as $s)
                    <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <!-- Orders List -->
        <div class="space-y-3">
            @forelse($orders as $order)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $order->code }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $order->visit_date->translatedFormat('d F Y') }} ·
                                {{ $order->items->count() }} item
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </p>
                            <p class="text-sm" style="color: {{ $order->status->color() }}">
                                {{ $order->status->label() }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <flux:button :href="route('pesanan.detail', $order)" variant="ghost" size="xs" wire:navigate>
                            Detail
                        </flux:button>
                        @if($order->isPaid())
                            <flux:button :href="route('e-tiket', $order)" variant="primary" size="xs" wire:navigate>
                                E-Tiket
                            </flux:button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="clipboard-document-list" class="mx-auto size-12 text-zinc-400" />
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Belum ada pesanan</h3>
                    <p class="mt-2 text-zinc-600 dark:text-zinc-400">Mulai pesan tiket untuk kunjungan Anda.</p>
                </div>
            @endforelse

            {{ $orders->links() }}
        </div>
    </div>
</div>

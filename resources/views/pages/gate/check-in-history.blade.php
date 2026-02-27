<?php

use App\Models\CheckIn;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Riwayat Check-in')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $date = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $checkIns = CheckIn::with(['order', 'checkedInBy'])
            ->when($this->date, fn ($q) => $q->whereDate('created_at', $this->date))
            ->when($this->search, fn ($q) => $q->whereHas('order', fn ($q2) => $q2->where('code', 'like', "%{$this->search}%")
                ->orWhere('customer_name', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(20);

        $todayCount = CheckIn::whereDate('created_at', $this->date ?: now()->toDateString())->count();

        $view->with([
            'checkIns' => $checkIns,
            'todayCount' => $todayCount,
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Riwayat Check-in</flux:heading>
            <div class="rounded-lg bg-blue-50 px-4 py-2 dark:bg-blue-900/20">
                <span class="text-sm text-blue-700 dark:text-blue-400">Total: <strong>{{ $todayCount }}</strong> check-in</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode pesanan atau nama..." icon="magnifying-glass" />
            </div>
            <flux:input wire:model.live="date" type="date" />
        </div>

        <!-- Table -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Waktu</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Kode Pesanan</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Pelanggan</th>
                            <th class="px-4 py-3 text-center font-medium text-zinc-600 dark:text-zinc-400">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Diperiksa Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($checkIns as $ci)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $ci->created_at->format('H:i:s') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-zinc-900 dark:text-white">
                                    {{ $ci->order->code }}
                                </td>
                                <td class="px-4 py-3 text-zinc-900 dark:text-white">
                                    {{ $ci->order->customer_name }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <flux:badge size="sm" :color="$ci->status->color()">{{ $ci->status->label() }}</flux:badge>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ $ci->checkedInBy?->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-400">Belum ada check-in.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $checkIns->links() }}</div>
    </div>
</div>

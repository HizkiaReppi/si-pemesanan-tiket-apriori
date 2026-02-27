<?php

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Audit Log')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $action = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        $this->resetPage();
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $logs = AuditLog::with('user')
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('auditable_type', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($q3) => $q3->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->action, fn ($q) => $q->where('action', $this->action))
            ->latest()
            ->paginate(20);

        $actions = AuditLog::distinct()->pluck('action')->filter()->sort()->values();

        $view->with([
            'logs' => $logs,
            'actions' => $actions,
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Audit Log</flux:heading>

        <!-- Filters -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pengguna atau model..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="action">
                <option value="">Semua Aksi</option>
                @foreach($actions as $act)
                    <option value="{{ $act }}">{{ ucfirst($act) }}</option>
                @endforeach
            </flux:select>
        </div>

        <!-- Table -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Waktu</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Pengguna</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Aksi</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Model</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">ID</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 text-zinc-900 dark:text-white">
                                    {{ $log->user?->name ?? 'System' }}
                                </td>
                                <td class="px-4 py-3">
                                    <flux:badge size="sm" :color="match($log->action) { 'created' => 'green', 'updated' => 'yellow', 'deleted' => 'red', default => 'zinc' }">
                                        {{ ucfirst($log->action) }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ class_basename($log->auditable_type) }}
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ $log->auditable_id }}
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
                                    @if($log->new_values)
                                        @php $changes = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true); @endphp
                                        @if(is_array($changes))
                                            {{ implode(', ', array_keys($changes)) }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-zinc-400">Belum ada log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</div>

<?php

use App\Models\TicketType;
use App\Models\TicketPrice;
use App\Enums\DayType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kelola Jenis Tiket')] #[Layout('layouts.app')] class extends Component {
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public float $weekdayPrice = 0;

    public float $weekendPrice = 0;

    public bool $isActive = true;

    public int $sortOrder = 0;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'weekdayPrice', 'weekendPrice', 'isActive', 'sortOrder']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $ticket = TicketType::with('prices')->findOrFail($id);
        $this->editingId = $ticket->id;
        $this->name = $ticket->name;
        $this->description = $ticket->description ?? '';
        $this->isActive = $ticket->is_active;
        $this->sortOrder = $ticket->sort_order;

        $weekdayPrice = $ticket->prices->firstWhere('day_type', DayType::Weekday);
        $weekendPrice = $ticket->prices->firstWhere('day_type', DayType::Weekend);
        $this->weekdayPrice = $weekdayPrice ? (float) $weekdayPrice->price : 0;
        $this->weekendPrice = $weekendPrice ? (float) $weekendPrice->price : 0;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'weekdayPrice' => 'required|numeric|min:0',
            'weekendPrice' => 'required|numeric|min:0',
        ]);

        $ticket = TicketType::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'description' => $this->description ?: null,
                'is_active' => $this->isActive,
                'sort_order' => $this->sortOrder,
            ]
        );

        TicketPrice::updateOrCreate(
            ['ticket_type_id' => $ticket->id, 'day_type' => DayType::Weekday],
            ['price' => $this->weekdayPrice]
        );

        TicketPrice::updateOrCreate(
            ['ticket_type_id' => $ticket->id, 'day_type' => DayType::Weekend],
            ['price' => $this->weekendPrice]
        );

        $this->showModal = false;
        session()->flash('success', $this->editingId ? 'Tiket berhasil diperbarui.' : 'Tiket berhasil ditambahkan.');
    }

    public function toggleActive(int $id): void
    {
        $ticket = TicketType::findOrFail($id);
        $ticket->update(['is_active' => ! $ticket->is_active]);
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->with('ticketTypes', TicketType::with('prices')->ordered()->get());
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Jenis Tiket</flux:heading>
            <flux:button wire:click="create" variant="primary" icon="plus" size="sm">Tambah Tiket</flux:button>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-3">
            @foreach($ticketTypes as $ticket)
                <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $ticket->name }}</h3>
                            @if(! $ticket->is_active)
                                <flux:badge size="sm" color="red">Nonaktif</flux:badge>
                            @endif
                        </div>
                        <div class="mt-1 flex gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                            @foreach($ticket->prices as $price)
                                <span>{{ $price->day_type->label() }}: Rp {{ number_format($price->price, 0, ',', '.') }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <flux:button wire:click="toggleActive({{ $ticket->id }})" variant="ghost" size="xs">
                            {{ $ticket->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </flux:button>
                        <flux:button wire:click="edit({{ $ticket->id }})" variant="ghost" size="xs" icon="pencil">
                            Edit
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
        <!-- Modal -->
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Tiket' : 'Tambah Tiket' }}</flux:heading>

                <flux:input wire:model="name" label="Nama Tiket" required />
                <flux:textarea wire:model="description" label="Deskripsi" rows="2" />
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="weekdayPrice" label="Harga Weekday (Rp)" type="number" min="0" required />
                    <flux:input wire:model="weekendPrice" label="Harga Weekend (Rp)" type="number" min="0" required />
                </div>
                <flux:input wire:model="sortOrder" label="Urutan" type="number" min="0" />
                <flux:checkbox wire:model="isActive" label="Aktif" />

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Batal</flux:button>
                    <flux:button wire:click="save" variant="primary">Simpan</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>

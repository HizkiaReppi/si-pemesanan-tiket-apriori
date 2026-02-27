<?php

use App\Models\Addon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kelola Add-on')] #[Layout('layouts.app')] class extends Component {
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public float $price = 0;

    public bool $isActive = true;

    public int $sortOrder = 0;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'price', 'isActive', 'sortOrder']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $addon = Addon::findOrFail($id);
        $this->editingId = $addon->id;
        $this->name = $addon->name;
        $this->description = $addon->description ?? '';
        $this->price = (float) $addon->price;
        $this->isActive = $addon->is_active;
        $this->sortOrder = $addon->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        Addon::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'description' => $this->description ?: null,
                'price' => $this->price,
                'is_active' => $this->isActive,
                'sort_order' => $this->sortOrder,
            ]
        );

        $this->showModal = false;
        session()->flash('success', $this->editingId ? 'Add-on berhasil diperbarui.' : 'Add-on berhasil ditambahkan.');
    }

    public function toggleActive(int $id): void
    {
        $addon = Addon::findOrFail($id);
        $addon->update(['is_active' => ! $addon->is_active]);
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->with('addons', Addon::ordered()->get());
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Add-on</flux:heading>
            <flux:button wire:click="create" variant="primary" icon="plus" size="sm">Tambah Add-on</flux:button>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-3">
            @foreach($addons as $addon)
                <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $addon->name }}</h3>
                            @if(! $addon->is_active)
                                <flux:badge size="sm" color="red">Nonaktif</flux:badge>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Rp {{ number_format($addon->price, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <flux:button wire:click="toggleActive({{ $addon->id }})" variant="ghost" size="xs">
                            {{ $addon->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </flux:button>
                        <flux:button wire:click="edit({{ $addon->id }})" variant="ghost" size="xs" icon="pencil">
                            Edit
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Add-on' : 'Tambah Add-on' }}</flux:heading>

                <flux:input wire:model="name" label="Nama Add-on" required />
                <flux:textarea wire:model="description" label="Deskripsi" rows="2" />
                <flux:input wire:model="price" label="Harga (Rp)" type="number" min="0" required />
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

<?php

use App\Models\Voucher;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kelola Voucher')] #[Layout('layouts.app')] class extends Component {
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $discountType = 'percentage';

    public float $discountValue = 0;

    public ?float $minOrder = null;

    public ?float $maxDiscount = null;

    public ?int $usageLimit = null;

    public string $validFrom = '';

    public string $validUntil = '';

    public bool $isActive = true;

    public function create(): void
    {
        $this->reset(['editingId', 'code', 'name', 'discountType', 'discountValue', 'minOrder', 'maxDiscount', 'usageLimit', 'validFrom', 'validUntil', 'isActive']);
        $this->isActive = true;
        $this->validFrom = now()->format('Y-m-d');
        $this->validUntil = now()->addMonth()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $voucher = Voucher::findOrFail($id);
        $this->editingId = $voucher->id;
        $this->code = $voucher->code;
        $this->name = $voucher->name;
        $this->discountType = $voucher->discount_type;
        $this->discountValue = (float) $voucher->discount_value;
        $this->minOrder = $voucher->min_order ? (float) $voucher->min_order : null;
        $this->maxDiscount = $voucher->max_discount ? (float) $voucher->max_discount : null;
        $this->usageLimit = $voucher->usage_limit;
        $this->validFrom = $voucher->valid_from->format('Y-m-d');
        $this->validUntil = $voucher->valid_until->format('Y-m-d');
        $this->isActive = $voucher->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'discountType' => 'required|in:percentage,fixed',
            'discountValue' => 'required|numeric|min:0',
            'validFrom' => 'required|date',
            'validUntil' => 'required|date|after:validFrom',
        ]);

        Voucher::updateOrCreate(
            ['id' => $this->editingId],
            [
                'code' => strtoupper($this->code),
                'name' => $this->name,
                'discount_type' => $this->discountType,
                'discount_value' => $this->discountValue,
                'min_order' => $this->minOrder,
                'max_discount' => $this->maxDiscount,
                'usage_limit' => $this->usageLimit,
                'valid_from' => $this->validFrom,
                'valid_until' => $this->validUntil,
                'is_active' => $this->isActive,
            ]
        );

        $this->showModal = false;
        session()->flash('success', $this->editingId ? 'Voucher berhasil diperbarui.' : 'Voucher berhasil ditambahkan.');
    }

    public function toggleActive(int $id): void
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->update(['is_active' => ! $voucher->is_active]);
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->with('vouchers', Voucher::latest()->get());
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Voucher</flux:heading>
            <flux:button wire:click="create" variant="primary" icon="plus" size="sm">Tambah Voucher</flux:button>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Kode</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Nama</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Diskon</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Penggunaan</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Berlaku</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td class="px-4 py-3 font-mono font-medium text-zinc-900 dark:text-white">{{ $voucher->code }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $voucher->name }}</td>
                            <td class="px-4 py-3 text-zinc-900 dark:text-white">
                                {{ $voucher->discount_type === 'percentage' ? $voucher->discount_value . '%' : 'Rp ' . number_format($voucher->discount_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $voucher->used_count }}{{ $voucher->usage_limit ? '/' . $voucher->usage_limit : '' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $voucher->valid_from->format('d/m/Y') }} - {{ $voucher->valid_until->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    <flux:button wire:click="edit({{ $voucher->id }})" variant="ghost" size="xs" icon="pencil" />
                                    <flux:button wire:click="toggleActive({{ $voucher->id }})" variant="ghost" size="xs">
                                        {{ $voucher->is_active ? 'Off' : 'On' }}
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500">Belum ada voucher.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Voucher' : 'Tambah Voucher' }}</flux:heading>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="code" label="Kode" required />
                    <flux:input wire:model="name" label="Nama" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model="discountType" label="Tipe Diskon">
                        <flux:select.option value="percentage">Persentase (%)</flux:select.option>
                        <flux:select.option value="fixed">Nominal (Rp)</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="discountValue" label="Nilai Diskon" type="number" min="0" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="minOrder" label="Min. Pesanan (Rp)" type="number" min="0" />
                    <flux:input wire:model="maxDiscount" label="Maks. Diskon (Rp)" type="number" min="0" />
                </div>
                <flux:input wire:model="usageLimit" label="Batas Penggunaan" type="number" min="0" />
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="validFrom" label="Berlaku Dari" type="date" required />
                    <flux:input wire:model="validUntil" label="Berlaku Sampai" type="date" required />
                </div>
                <flux:checkbox wire:model="isActive" label="Aktif" />

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Batal</flux:button>
                    <flux:button wire:click="save" variant="primary">Simpan</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>

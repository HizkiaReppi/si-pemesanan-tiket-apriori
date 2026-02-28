<?php

use App\Models\Gallery;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Manajemen Galeri')] #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|image|max:5120')]
    public $image = null;

    #[Validate('integer|min:0')]
    public int $sortOrder = 0;

    public bool $isActive = true;

    public string $search = '';

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();

        if ($id) {
            $gallery = Gallery::findOrFail($id);
            $this->editingId = $gallery->id;
            $this->title = $gallery->title;
            $this->description = $gallery->description ?? '';
            $this->sortOrder = $gallery->sort_order;
            $this->isActive = $gallery->is_active;
            $this->image = null;
        } else {
            $this->editingId = null;
            $this->title = '';
            $this->description = '';
            $this->sortOrder = 0;
            $this->isActive = true;
            $this->image = null;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sortOrder' => 'integer|min:0',
        ];

        if (! $this->editingId) {
            $rules['image'] = 'required|image|max:5120';
        } else {
            $rules['image'] = 'nullable|image|max:5120';
        }

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];

        if ($this->image) {
            $data['image_path'] = $this->image->store('galleries', 'public');
        }

        if ($this->editingId) {
            $gallery = Gallery::findOrFail($this->editingId);

            // Delete old image if uploading new one
            if ($this->image && $gallery->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($gallery->image_path);
            }

            $gallery->update($data);
            session()->flash('success', 'Foto galeri berhasil diperbarui.');
        } else {
            $data['uploaded_by'] = auth()->id();
            Gallery::create($data);
            session()->flash('success', 'Foto galeri berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['title', 'description', 'image', 'sortOrder', 'isActive', 'editingId']);
    }

    public function toggleActive(int $id): void
    {
        $gallery = Gallery::findOrFail($id);
        $gallery->update(['is_active' => ! $gallery->is_active]);
    }

    public function delete(int $id): void
    {
        $gallery = Gallery::findOrFail($id);

        if ($gallery->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();
        session()->flash('success', 'Foto galeri berhasil dihapus.');
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $galleries = Gallery::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->ordered()
            ->paginate(12);

        $view->with('galleries', $galleries);
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Manajemen Galeri</flux:heading>
            <flux:button wire:click="openModal" variant="primary" icon="plus">
                Tambah Foto
            </flux:button>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search -->
        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari foto..." icon="magnifying-glass" class="max-w-sm" />
        </div>

        <!-- Gallery Grid -->
        @if($galleries->isEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <flux:icon name="photo" class="mx-auto size-12 text-zinc-400" />
                <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Belum Ada Foto</h3>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">Mulai tambahkan foto ke galeri.</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($galleries as $gallery)
                    <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="aspect-video overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($gallery->image_path) }}"
                                alt="{{ $gallery->title }}"
                                class="size-full object-cover transition-transform group-hover:scale-105"
                                loading="lazy"
                            />
                        </div>
                        <div class="p-4">
                            <div class="mb-2 flex items-start justify-between">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $gallery->title }}</h3>
                                <flux:badge size="sm" :color="$gallery->is_active ? 'green' : 'zinc'">
                                    {{ $gallery->is_active ? 'Aktif' : 'Nonaktif' }}
                                </flux:badge>
                            </div>
                            @if($gallery->description)
                                <p class="mb-3 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $gallery->description }}</p>
                            @endif
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="openModal({{ $gallery->id }})" size="xs" variant="ghost" icon="pencil-square">
                                    Edit
                                </flux:button>
                                <flux:button wire:click="toggleActive({{ $gallery->id }})" size="xs" variant="ghost" icon="{{ $gallery->is_active ? 'eye-slash' : 'eye' }}">
                                    {{ $gallery->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                                </flux:button>
                                <flux:button wire:click="delete({{ $gallery->id }})" wire:confirm="Hapus foto ini?" size="xs" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $galleries->links() }}
            </div>
        @endif
    </div>

    <!-- Add/Edit Modal -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Edit Foto' : 'Tambah Foto Baru' }}</flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="title" label="Judul" placeholder="Masukkan judul foto" required />

                <flux:textarea wire:model="description" label="Deskripsi (Opsional)" placeholder="Deskripsi singkat foto..." rows="3" />

                <div>
                    <flux:label>Foto{{ $editingId ? ' (Kosongkan jika tidak ingin mengubah)' : '' }}</flux:label>
                    <input
                        type="file"
                        wire:model="image"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-sky-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-sky-700 hover:file:bg-sky-100 dark:text-zinc-400 dark:file:bg-sky-900/30 dark:file:text-sky-400"
                    />
                    @error('image')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    @if($image)
                        <div class="mt-2">
                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="h-32 rounded-lg object-cover" />
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="sortOrder" label="Urutan" type="number" min="0" />
                    <div class="flex items-end pb-1">
                        <flux:checkbox wire:model="isActive" label="Aktif" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Batal</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Simpan Perubahan' : 'Tambah Foto' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

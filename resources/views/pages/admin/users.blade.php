<?php

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Kelola Pengguna')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = 'customer';

    public string $password = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'role', 'password']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role->value;
        $this->password = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->editingId,
            'role' => 'required|in:' . implode(',', array_column(UserRole::cases(), 'value')),
        ];

        if (! $this->editingId) {
            $rules['password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'role' => $this->role,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        User::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        session()->flash('success', $this->editingId ? 'Pengguna berhasil diperbarui.' : 'Pengguna berhasil ditambahkan.');
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $query = User::latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        $view->with('users', $query->paginate(15));
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Pengguna</flux:heading>
            <flux:button wire:click="create" variant="primary" icon="plus" size="sm">Tambah Pengguna</flux:button>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama/email..." icon="magnifying-glass" class="max-w-xs" />
            <flux:select wire:model.live="roleFilter" class="max-w-[200px]">
                <flux:select.option value="">Semua Role</flux:select.option>
                @foreach(UserRole::cases() as $r)
                    <flux:select.option value="{{ $r->value }}">{{ $r->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Nama</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Role</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Terdaftar</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm">{{ $user->role->label() }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <flux:button wire:click="edit({{ $user->id }})" variant="ghost" size="xs" icon="pencil">
                                    Edit
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500">Tidak ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Pengguna' : 'Tambah Pengguna' }}</flux:heading>

                <flux:input wire:model="name" label="Nama" required />
                <flux:input wire:model="email" label="Email" type="email" required />
                <flux:input wire:model="phone" label="No. Telepon" />
                <flux:select wire:model="role" label="Role">
                    @foreach(UserRole::cases() as $r)
                        <flux:select.option value="{{ $r->value }}">{{ $r->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="password" label="{{ $editingId ? 'Password (kosongkan jika tidak diubah)' : 'Password' }}" type="password" :required="!$editingId" />

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Batal</flux:button>
                    <flux:button wire:click="save" variant="primary">Simpan</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>

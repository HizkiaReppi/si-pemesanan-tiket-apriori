<?php

use App\Enums\CheckInStatus;
use App\Enums\UserRole;
use App\Models\CheckIn;
use App\Models\Payment;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Manajemen Staff')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $performancePeriod = 'month';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = 'admin';

    public string $password = '';

    public ?int $viewingStaffId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'role', 'password']);
        $this->role = 'admin';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        if (! in_array($user->role, [UserRole::Admin, UserRole::Cashier, UserRole::GateStaff])) {
            return;
        }

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
            'role' => 'required|in:admin,cashier,gate_staff',
            'phone' => 'nullable|string|max:20',
        ];

        if (! $this->editingId) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
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

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
        } else {
            $data['email_verified_at'] = now();
            User::create($data);
        }

        $this->showModal = false;
        session()->flash('success', $this->editingId ? 'Data staff berhasil diperbarui.' : 'Staff baru berhasil ditambahkan.');
    }

    public function viewPerformance(?int $id): void
    {
        $this->viewingStaffId = $this->viewingStaffId === $id ? null : $id;
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $staffRoles = [UserRole::Admin, UserRole::Cashier, UserRole::GateStaff];

        $query = User::whereIn('role', $staffRoles)->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        $staff = $query->paginate(15);

        // Performance data
        $periodStart = match ($this->performancePeriod) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        // Staff summary stats
        $totalStaff = User::whereIn('role', $staffRoles)->count();
        $totalAdmins = User::where('role', UserRole::Admin)->count();
        $totalCashiers = User::where('role', UserRole::Cashier)->count();
        $totalGateStaff = User::where('role', UserRole::GateStaff)->count();

        // Performance metrics per staff
        $staffPerformance = [];
        foreach ($staff as $member) {
            $performance = [];

            if ($member->role === UserRole::Cashier) {
                // Cashier: payments processed (verified), revenue handled
                $paymentsProcessed = Payment::where('verified_by', $member->id)
                    ->where('created_at', '>=', $periodStart)
                    ->count();

                $revenueHandled = Payment::where('verified_by', $member->id)
                    ->where('status', 'paid')
                    ->where('created_at', '>=', $periodStart)
                    ->sum('amount');

                $performance = [
                    'payments_processed' => $paymentsProcessed,
                    'revenue_handled' => $revenueHandled,
                ];
            } elseif ($member->role === UserRole::GateStaff) {
                // Gate staff: check-ins scanned
                $totalScans = CheckIn::where('scanned_by', $member->id)
                    ->where('created_at', '>=', $periodStart)
                    ->count();

                $successfulScans = CheckIn::where('scanned_by', $member->id)
                    ->where('status', CheckInStatus::Valid)
                    ->where('created_at', '>=', $periodStart)
                    ->count();

                $performance = [
                    'total_scans' => $totalScans,
                    'successful_scans' => $successfulScans,
                    'success_rate' => $totalScans > 0 ? round(($successfulScans / $totalScans) * 100, 1) : 0,
                ];
            } elseif ($member->role === UserRole::Admin) {
                // Admin: payments verified
                $paymentsVerified = Payment::where('verified_by', $member->id)
                    ->where('created_at', '>=', $periodStart)
                    ->count();

                $performance = [
                    'payments_verified' => $paymentsVerified,
                ];
            }

            $staffPerformance[$member->id] = $performance;
        }

        $view->with([
            'staff' => $staff,
            'totalStaff' => $totalStaff,
            'totalAdmins' => $totalAdmins,
            'totalCashiers' => $totalCashiers,
            'totalGateStaff' => $totalGateStaff,
            'staffPerformance' => $staffPerformance,
            'staffRoles' => [UserRole::Admin, UserRole::Cashier, UserRole::GateStaff],
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Manajemen Staff</flux:heading>
            <flux:button wire:click="create" variant="primary" icon="plus" size="sm">Tambah Staff</flux:button>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <!-- Staff Overview -->
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Staff</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalStaff }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Administrator</p>
                <p class="mt-1 text-2xl font-bold text-sky-600">{{ $totalAdmins }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Kasir</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ $totalCashiers }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Petugas Gerbang</p>
                <p class="mt-1 text-2xl font-bold text-green-600">{{ $totalGateStaff }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-end gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama/email..." icon="magnifying-glass" class="max-w-xs" />
            <flux:select wire:model.live="roleFilter" class="max-w-50">
                <flux:select.option value="">Semua Role</flux:select.option>
                @foreach($staffRoles as $r)
                    <flux:select.option value="{{ $r->value }}">{{ $r->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="performancePeriod" class="max-w-45">
                <flux:select.option value="week">1 Minggu</flux:select.option>
                <flux:select.option value="month">1 Bulan</flux:select.option>
                <flux:select.option value="quarter">3 Bulan</flux:select.option>
                <flux:select.option value="year">1 Tahun</flux:select.option>
            </flux:select>
        </div>

        <!-- Staff List -->
        <div class="space-y-4">
            @forelse($staff as $member)
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-4">
                            <div class="flex size-12 items-center justify-center rounded-full bg-zinc-100 text-lg font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($member->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $member->name }}</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $member->email }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <flux:badge size="sm" :color="match($member->role) { \App\Enums\UserRole::Admin => 'blue', \App\Enums\UserRole::Cashier => 'amber', \App\Enums\UserRole::GateStaff => 'green', default => 'zinc' }">
                                        {{ $member->role->label() }}
                                    </flux:badge>
                                    @if($member->phone)
                                        <span class="text-xs text-zinc-400">{{ $member->phone }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="viewPerformance({{ $member->id }})" size="xs" variant="ghost" icon="chart-bar">
                                Kinerja
                            </flux:button>
                            <flux:button wire:click="edit({{ $member->id }})" size="xs" variant="ghost" icon="pencil-square">
                                Edit
                            </flux:button>
                        </div>
                    </div>

                    <!-- Performance Detail (expandable) -->
                    @if($viewingStaffId === $member->id)
                        <div class="border-t border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <h4 class="mb-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                Kinerja ({{ match($performancePeriod) { 'week' => '7 Hari Terakhir', 'month' => '30 Hari Terakhir', 'quarter' => '3 Bulan Terakhir', 'year' => '1 Tahun Terakhir', default => '' } }})
                            </h4>

                            @php $perf = $staffPerformance[$member->id] ?? []; @endphp

                            @if($member->role === \App\Enums\UserRole::Cashier)
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Pembayaran Diproses</p>
                                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($perf['payments_processed'] ?? 0) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Pendapatan Ditangani</p>
                                        <p class="text-2xl font-bold text-green-600">Rp {{ number_format($perf['revenue_handled'] ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @elseif($member->role === \App\Enums\UserRole::GateStaff)
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Scan</p>
                                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($perf['total_scans'] ?? 0) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Scan Berhasil</p>
                                        <p class="text-2xl font-bold text-green-600">{{ number_format($perf['successful_scans'] ?? 0) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Tingkat Keberhasilan</p>
                                        <p class="text-2xl font-bold text-sky-600">{{ $perf['success_rate'] ?? 0 }}%</p>
                                    </div>
                                </div>
                            @elseif($member->role === \App\Enums\UserRole::Admin)
                                <div class="grid gap-4 sm:grid-cols-1">
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Pembayaran Diverifikasi</p>
                                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($perf['payments_verified'] ?? 0) }}</p>
                                    </div>
                                </div>
                            @endif

                            <p class="mt-3 text-xs text-zinc-400">Bergabung: {{ $member->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="user-group" class="mx-auto size-12 text-zinc-400" />
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Belum Ada Staff</h3>
                    <p class="mt-2 text-zinc-600 dark:text-zinc-400">Tambahkan staff baru untuk mengelola operasional.</p>
                </div>
            @endforelse
        </div>

        {{ $staff->links() }}
    </div>

    <!-- Add/Edit Modal -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Edit Staff' : 'Tambah Staff Baru' }}</flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="name" label="Nama Lengkap" required />
                <flux:input wire:model="email" label="Email" type="email" required />
                <flux:input wire:model="phone" label="No. Telepon" />
                <flux:select wire:model="role" label="Role">
                    <flux:select.option value="admin">Administrator</flux:select.option>
                    <flux:select.option value="cashier">Kasir</flux:select.option>
                    <flux:select.option value="gate_staff">Petugas Gerbang</flux:select.option>
                </flux:select>
                <flux:input wire:model="password" label="{{ $editingId ? 'Password (kosongkan jika tidak diubah)' : 'Password' }}" type="password" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Batal</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Simpan Perubahan' : 'Tambah Staff' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

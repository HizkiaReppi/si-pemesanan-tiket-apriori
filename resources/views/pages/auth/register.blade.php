<x-layouts::auth :title="'Daftar'">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Buat akun baru" description="Lengkapi data di bawah untuk mendaftar" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                label="Nama Lengkap"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama lengkap Anda"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Alamat Email"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@contoh.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                label="Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                label="Konfirmasi Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Ulangi kata sandi"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    Buat Akun
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>Sudah punya akun?</span>
            <flux:link :href="route('login')" wire:navigate>Masuk</flux:link>
        </div>
    </div>
</x-layouts::auth>

<x-layouts::auth :title="'Reset Kata Sandi'">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Reset kata sandi" description="Masukkan kata sandi baru Anda di bawah" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                label="Email"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                label="Kata Sandi Baru"
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
                placeholder="Ulangi kata sandi baru"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    Reset Kata Sandi
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>

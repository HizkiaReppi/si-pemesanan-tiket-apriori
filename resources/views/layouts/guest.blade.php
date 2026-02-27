<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
        <!-- Navigation -->
        <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/90">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                        <x-app-logo-icon class="size-8" />
                        <span class="text-lg font-semibold text-zinc-900 dark:text-white">Kolam Po'opo</span>
                    </a>

                    <nav class="hidden items-center gap-1 md:flex">
                        <flux:button variant="ghost" :href="route('tiket.index')" wire:navigate size="sm">
                            Tiket
                        </flux:button>
                        <flux:button variant="ghost" :href="route('tentang')" wire:navigate size="sm">
                            Tentang
                        </flux:button>
                        <flux:button variant="ghost" :href="route('faq')" wire:navigate size="sm">
                            FAQ
                        </flux:button>
                        <flux:button variant="ghost" :href="route('kontak')" wire:navigate size="sm">
                            Kontak
                        </flux:button>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button variant="primary" :href="route('tiket.index')" wire:navigate size="sm" icon="ticket">
                        Pesan Tiket
                    </flux:button>

                    @auth
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" icon="user" size="sm">
                                {{ auth()->user()->name }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item :href="route('dashboard')" icon="squares-2x2" wire:navigate>
                                    Dashboard
                                </flux:menu.item>
                                <flux:menu.item :href="route('pesanan-saya')" icon="clipboard-document-list" wire:navigate>
                                    Pesanan Saya
                                </flux:menu.item>
                                <flux:menu.separator />
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                                        Keluar
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    @else
                        <flux:button variant="ghost" :href="route('login')" wire:navigate size="sm">
                            Masuk
                        </flux:button>
                        <flux:button variant="primary" :href="route('register')" wire:navigate size="sm">
                            Daftar
                        </flux:button>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="relative overflow-hidden border-t border-sky-200 dark:border-sky-900">
            <!-- Pool-themed background -->
            <div class="absolute inset-0 bg-gradient-to-b from-sky-50 via-sky-100/80 to-sky-200/60 dark:from-zinc-900 dark:via-sky-950/40 dark:to-zinc-900">
                <svg class="absolute inset-0 h-full w-full opacity-[0.08] dark:opacity-[0.05]" viewBox="0 0 1440 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="200" cy="350" r="220" fill="currentColor" class="text-sky-500"/>
                    <circle cx="600" cy="120" r="160" fill="currentColor" class="text-sky-400"/>
                    <circle cx="1000" cy="400" r="200" fill="currentColor" class="text-sky-600"/>
                    <circle cx="1300" cy="180" r="140" fill="currentColor" class="text-sky-300"/>
                    <circle cx="400" cy="80" r="100" fill="currentColor" class="text-sky-500"/>
                    <circle cx="850" cy="300" r="180" fill="currentColor" class="text-sky-400"/>
                    <path d="M0 200C200 230 400 180 600 210C800 240 1000 190 1200 220C1320 235 1380 225 1440 230V500H0Z" fill="currentColor" class="text-sky-400" opacity="0.15"/>
                    <path d="M0 260C240 280 480 250 720 270C960 290 1200 260 1440 280V500H0Z" fill="currentColor" class="text-sky-500" opacity="0.1"/>
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Brand -->
                    <div class="lg:col-span-1">
                        <div class="flex items-center gap-2">
                            <x-app-logo-icon class="size-8" />
                            <span class="text-lg font-bold text-zinc-900 dark:text-white">Kolam Po'opo</span>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                            Wisata kolam air panas alami terbaik di Sulawesi Utara. Nikmati pengalaman berendam yang menyegarkan di tengah keindahan alam tropis.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white">Navigasi</h3>
                        <ul class="mt-3 space-y-2">
                            <li><a href="{{ route('home') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">Beranda</a></li>
                            <li><a href="{{ route('tiket.index') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">Beli Tiket</a></li>
                            <li><a href="{{ route('tentang') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">Tentang Kami</a></li>
                            <li><a href="{{ route('faq') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">FAQ</a></li>
                            <li><a href="{{ route('kontak') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">Hubungi Kami</a></li>
                        </ul>
                    </div>

                    <!-- Info -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white">Informasi</h3>
                        <ul class="mt-3 space-y-2">
                            <li><a href="{{ route('syarat-ketentuan') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">Syarat & Ketentuan</a></li>
                            <li><a href="{{ route('kebijakan-privasi') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">Kebijakan Privasi</a></li>
                        </ul>

                        <h3 class="mt-6 text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white">Jam Operasional</h3>
                        <div class="mt-3 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <p>Senin - Jumat: 08:00 - 18:00 WITA</p>
                            <p>Sabtu - Minggu: 07:00 - 19:00 WITA</p>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-white">Kontak</h3>
                        <div class="mt-3 space-y-3">
                            <div class="flex items-start gap-2">
                                <flux:icon name="map-pin" variant="mini" class="mt-0.5 size-4 text-sky-600 dark:text-sky-400" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Sulawesi Utara, Indonesia</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <flux:icon name="phone" variant="mini" class="mt-0.5 size-4 text-sky-600 dark:text-sky-400" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">+62 812-3456-7890</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <flux:icon name="envelope" variant="mini" class="mt-0.5 size-4 text-sky-600 dark:text-sky-400" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">info@kolampoopo.id</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 border-t border-sky-200/50 pt-6 dark:border-sky-900/50">
                    <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <p class="text-center text-xs text-zinc-500 dark:text-zinc-400">
                            &copy; {{ date('Y') }} Kolam Po'opo. All rights reserved.
                        </p>
                        <div class="flex gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                            <a href="{{ route('syarat-ketentuan') }}" wire:navigate class="transition hover:text-sky-600 dark:hover:text-sky-400">Syarat & Ketentuan</a>
                            <a href="{{ route('kebijakan-privasi') }}" wire:navigate class="transition hover:text-sky-600 dark:hover:text-sky-400">Kebijakan Privasi</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>

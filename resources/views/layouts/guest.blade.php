<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-linear-to-br from-slate-50 via-white to-sky-50/30 antialiased dark:from-zinc-950 dark:via-zinc-900 dark:to-slate-900">
        <!-- Animated Background Decorations -->
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <div class="animate-float-gentle absolute -right-32 -top-32 size-112 rounded-full bg-sky-200/40 blur-3xl dark:bg-sky-800/20"></div>
            <div class="animate-float-slow absolute -left-24 top-1/3 size-88 rounded-full bg-cyan-200/30 blur-3xl dark:bg-cyan-800/15"></div>
            <div class="animate-float-delayed absolute bottom-1/4 right-1/4 size-104 rounded-full bg-teal-200/25 blur-3xl dark:bg-teal-800/15"></div>
            <div class="animate-float-reverse absolute left-1/3 top-2/3 size-72 rounded-full bg-indigo-200/20 blur-3xl dark:bg-indigo-900/10"></div>
            <div class="animate-pulse-soft absolute -bottom-20 -left-20 size-80 rounded-full bg-sky-300/20 blur-3xl dark:bg-sky-900/15"></div>
            <div class="bg-dot-pattern absolute inset-0"></div>
        </div>

        <!-- Navigation -->
        <header
            x-data="{ mobileOpen: false }"
            class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/80 backdrop-blur-xl dark:border-zinc-700/80 dark:bg-zinc-900/80"
        >
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                        <x-app-logo-icon class="size-8" />
                        <span class="text-lg font-semibold text-zinc-900 dark:text-white">Kolam Po'opo</span>
                    </a>

                    <nav class="hidden items-center gap-1 md:flex">
                        <flux:button variant="ghost" :href="route('home')" wire:navigate size="sm">
                            Beranda
                        </flux:button>
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
                        <flux:button variant="ghost" :href="route('cek-pesanan')" wire:navigate size="sm">
                            Cek Pesanan
                        </flux:button>
                    </nav>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Theme Toggle -->
                    <flux:dropdown position="bottom" align="end">
                        <flux:button variant="ghost" size="sm" class="size-9! p-0!" aria-label="Ganti tema">
                            <span class="block dark:hidden">
                                <flux:icon name="moon" variant="mini" class="size-5" />
                            </span>
                            <span class="hidden dark:block">
                                <flux:icon name="sun" variant="mini" class="size-5" />
                            </span>
                        </flux:button>
                        <flux:menu>
                            <flux:menu.heading>Tema</flux:menu.heading>
                            <flux:menu.item x-on:click="$flux.appearance = 'light'" icon="sun">
                                Terang
                            </flux:menu.item>
                            <flux:menu.item x-on:click="$flux.appearance = 'dark'" icon="moon">
                                Gelap
                            </flux:menu.item>
                            <flux:menu.item x-on:click="$flux.appearance = 'system'" icon="computer-desktop">
                                Sistem
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    <flux:button variant="primary" :href="route('tiket.index')" wire:navigate size="sm" icon="ticket" class="hidden sm:inline-flex">
                        Pesan Tiket
                    </flux:button>

                    @auth
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" icon="user" size="sm" class="hidden sm:inline-flex">
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
                        <flux:button variant="ghost" :href="route('login')" wire:navigate size="sm" class="hidden sm:inline-flex">
                            Masuk
                        </flux:button>
                    @endauth

                    <!-- Mobile Menu Toggle -->
                    <button
                        x-on:click="mobileOpen = !mobileOpen"
                        class="flex size-9 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800 md:hidden"
                        aria-label="Menu"
                    >
                        <svg x-show="!mobileOpen" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div
                x-show="mobileOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="border-t border-zinc-200/80 bg-white/95 backdrop-blur-xl dark:border-zinc-700/80 dark:bg-zinc-900/95 md:hidden"
            >
                <nav class="mx-auto max-w-7xl space-y-1 px-4 pb-4 pt-3">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <flux:icon name="home" variant="mini" class="size-5 text-zinc-400" />
                        Beranda
                    </a>
                    <a href="{{ route('tiket.index') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <flux:icon name="ticket" variant="mini" class="size-5 text-sky-500" />
                        Pesan Tiket
                    </a>
                    <a href="{{ route('tentang') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <flux:icon name="information-circle" variant="mini" class="size-5 text-zinc-400" />
                        Tentang
                    </a>
                    <a href="{{ route('faq') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <flux:icon name="question-mark-circle" variant="mini" class="size-5 text-zinc-400" />
                        FAQ
                    </a>
                    <a href="{{ route('kontak') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <flux:icon name="envelope" variant="mini" class="size-5 text-zinc-400" />
                        Kontak
                    </a>
                    <a href="{{ route('cek-pesanan') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <flux:icon name="magnifying-glass" variant="mini" class="size-5 text-zinc-400" />
                        Cek Pesanan
                    </a>

                    <div class="my-2 border-t border-zinc-200 dark:border-zinc-700"></div>

                    @auth
                        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <flux:icon name="squares-2x2" variant="mini" class="size-5 text-zinc-400" />
                            Dashboard
                        </a>
                        <a href="{{ route('pesanan-saya') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <flux:icon name="clipboard-document-list" variant="mini" class="size-5 text-zinc-400" />
                            Pesanan Saya
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                <flux:icon name="arrow-right-start-on-rectangle" variant="mini" class="size-5" />
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-right-end-on-rectangle" variant="mini" class="size-5 text-zinc-400" />
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" wire:navigate class="flex items-center gap-3 rounded-lg bg-sky-600 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700">
                            <flux:icon name="user-plus" variant="mini" class="size-5" />
                            Daftar
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="relative overflow-hidden border-t border-sky-200/70 dark:border-sky-900/50">
            <!-- Pool-themed background with animated elements -->
            <div class="absolute inset-0 bg-linear-to-b from-sky-50 via-sky-100/80 to-sky-200/60 dark:from-zinc-900 dark:via-sky-950/40 dark:to-zinc-900">
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
                <!-- Animated floating orbs in footer -->
                <div class="animate-float-gentle absolute right-10 top-10 size-32 rounded-full bg-sky-300/10 blur-2xl dark:bg-sky-700/10"></div>
                <div class="animate-float-slow absolute bottom-10 left-20 size-24 rounded-full bg-cyan-300/10 blur-2xl dark:bg-cyan-700/10"></div>
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
                            <li><a href="{{ route('cek-pesanan') }}" wire:navigate class="text-sm text-zinc-600 transition hover:text-sky-600 dark:text-zinc-400 dark:hover:text-sky-400">Cek Pesanan</a></li>
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
                            <p>Setiap Hari: 08:00 - 22:00 WITA</p>
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

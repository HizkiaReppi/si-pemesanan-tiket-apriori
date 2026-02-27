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
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button variant="ghost" :href="route('keranjang')" wire:navigate size="sm" icon="shopping-cart">
                        Keranjang
                        @if(app(\App\Services\CartService::class)->getItemCount() > 0)
                            <flux:badge size="sm" color="red" inset="top right">
                                {{ app(\App\Services\CartService::class)->getItemCount() }}
                            </flux:badge>
                        @endif
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
        <footer class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-3">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Kolam Po'opo</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Kolam renang air panas alami di Sulawesi Utara. Nikmati pengalaman berendam yang menyegarkan.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Jam Operasional</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Setiap Hari: 08:00 - 18:00 WITA
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Kontak</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Sulawesi Utara, Indonesia
                        </p>
                    </div>
                </div>
                <div class="mt-8 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <p class="text-center text-xs text-zinc-500 dark:text-zinc-400">
                        &copy; {{ date('Y') }} Kolam Po'opo. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>

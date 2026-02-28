<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                {{-- Customer Navigation --}}
                @if(auth()->user()->isCustomer())
                    <flux:sidebar.group heading="Pembelian" class="grid">
                        <flux:sidebar.item icon="ticket" :href="route('tiket.index')" :current="request()->routeIs('tiket.*')" wire:navigate>
                            Beli Tiket
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('pesanan-saya')" :current="request()->routeIs('pesanan-saya')" wire:navigate>
                            Pesanan Saya
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                {{-- Admin Navigation --}}
                @if(auth()->user()->isAdmin())
                    <flux:sidebar.group heading="Admin" class="grid">
                        <flux:sidebar.item icon="squares-2x2" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            Dashboard
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.pesanan')" :current="request()->routeIs('admin.pesanan*')" wire:navigate>
                            Pesanan
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('admin.verifikasi')" :current="request()->routeIs('admin.verifikasi')" wire:navigate>
                            Verifikasi Pembayaran
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Kelola Produk" class="grid">
                        <flux:sidebar.item icon="ticket" :href="route('admin.tiket')" :current="request()->routeIs('admin.tiket')" wire:navigate>
                            Jenis Tiket
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cube" :href="route('admin.addon')" :current="request()->routeIs('admin.addon')" wire:navigate>
                            Add-on
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="tag" :href="route('admin.voucher')" :current="request()->routeIs('admin.voucher')" wire:navigate>
                            Voucher
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="photo" :href="route('admin.galeri')" :current="request()->routeIs('admin.galeri')" wire:navigate>
                            Galeri
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Analisis & Laporan" class="grid">
                        <flux:sidebar.item icon="cpu-chip" :href="route('admin.apriori')" :current="request()->routeIs('admin.apriori')" wire:navigate>
                            Analisis Apriori
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-bar" :href="route('admin.laporan')" :current="request()->routeIs('admin.laporan')" wire:navigate>
                            Laporan
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Sistem" class="grid">
                        <flux:sidebar.item icon="users" :href="route('admin.pengguna')" :current="request()->routeIs('admin.pengguna')" wire:navigate>
                            Pelanggan
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('admin.audit-log')" :current="request()->routeIs('admin.audit-log')" wire:navigate>
                            Audit Log
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                {{-- Cashier Navigation --}}
                @if(auth()->user()->isCashier())
                    <flux:sidebar.group heading="Kasir" class="grid">
                        @if(auth()->user()->role->value === 'cashier')
                            <flux:sidebar.item icon="squares-2x2" :href="route('kasir.dashboard')" :current="request()->routeIs('kasir.dashboard')" wire:navigate>
                                Dashboard
                            </flux:sidebar.item>
                        @endif
                        <flux:sidebar.item icon="shopping-cart" :href="route('kasir.penjualan')" :current="request()->routeIs('kasir.penjualan')" wire:navigate>
                            Penjualan
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('kasir.pesanan')" :current="request()->routeIs('kasir.pesanan')" wire:navigate>
                            Pesanan Kasir
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('kasir.verifikasi')" :current="request()->routeIs('kasir.verifikasi')" wire:navigate>
                            Verifikasi Pembayaran
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                {{-- Gate Staff Navigation --}}
                @if(auth()->user()->isGateStaff())
                    <flux:sidebar.group heading="Gerbang" class="grid">
                        <flux:sidebar.item icon="qr-code" :href="route('gerbang.scanner')" :current="request()->routeIs('gerbang.scanner')" wire:navigate>
                            Scan Tiket
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('gerbang.riwayat')" :current="request()->routeIs('gerbang.riwayat')" wire:navigate>
                            Riwayat Check-in
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                {{-- Owner Navigation --}}
                @if(auth()->user()->isOwner())
                    <flux:sidebar.group heading="Pemilik" class="grid">
                        @if(auth()->user()->role->value === 'owner')
                            <flux:sidebar.item icon="squares-2x2" :href="route('pemilik.dashboard')" :current="request()->routeIs('pemilik.dashboard')" wire:navigate>
                                Dashboard
                            </flux:sidebar.item>
                        @endif
                        <flux:sidebar.item icon="chart-bar" :href="route('pemilik.laporan')" :current="request()->routeIs('pemilik.laporan')" wire:navigate>
                            Laporan
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cpu-chip" :href="route('pemilik.apriori')" :current="request()->routeIs('pemilik.apriori')" wire:navigate>
                            Analisis Apriori
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="user-group" :href="route('pemilik.staf')" :current="request()->routeIs('pemilik.staf')" wire:navigate>
                            Manajemen Staff
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                {{-- Public Links --}}
                <flux:sidebar.group heading="Lainnya" class="grid">
                    <flux:sidebar.item icon="home" :href="route('home')" wire:navigate>
                        Beranda
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                    Pengaturan
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            Pengaturan
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            Keluar
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>

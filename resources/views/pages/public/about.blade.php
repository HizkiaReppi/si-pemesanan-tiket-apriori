<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tentang Kami - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
}; ?>

<div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-linear-to-br from-sky-600 via-sky-700 to-sky-900 py-24">
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 1440 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="300" cy="200" r="180" fill="currentColor" class="text-white" />
                <circle cx="1100" cy="100" r="120" fill="currentColor" class="text-white" />
                <circle cx="700" cy="400" r="200" fill="currentColor" class="text-white" />
            </svg>
        </div>
        <!-- Animated floating orbs -->
        <div class="animate-float-gentle absolute -top-10 left-1/4 size-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="animate-float-slow absolute -bottom-10 right-1/3 size-52 rounded-full bg-sky-400/10 blur-2xl"></div>
        <div class="animate-float-reverse absolute right-10 top-10 size-24 rounded-full bg-sky-300/10 blur-xl"></div>
        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium text-sky-100 backdrop-blur-sm">
                <flux:icon name="information-circle" variant="mini" class="size-4" />
                Tentang Kami
            </span>
            <h1 class="mt-4 text-4xl font-bold text-white sm:text-5xl lg:text-6xl">Kolam Po'opo</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-sky-100">
                Wisata kolam air panas alami terbaik di Sulawesi Utara &mdash; tempat rekreasi keluarga yang mengedepankan kenyamanan dan kelestarian alam.
            </p>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 60L1440 60L1440 0C1200 50 240 50 0 0L0 60Z" class="fill-white dark:fill-zinc-800"/>
            </svg>
        </div>
    </section>

    <!-- Sejarah -->
    <section class="relative bg-white py-16 dark:bg-zinc-800 sm:py-24">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <span class="text-sm font-semibold uppercase tracking-wider text-sky-600 dark:text-sky-400">Sejarah Kami</span>
                    <h2 class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">Kolam Air Panas Alami yang Kaya Mineral</h2>
                    <div class="mt-6 space-y-4 text-zinc-600 dark:text-zinc-400">
                        <p>
                            Kolam Po'opo merupakan destinasi wisata kolam air panas alami yang terletak di Sulawesi Utara.
                            Dengan sumber air panas alami yang kaya mineral, Kolam Po'opo telah menjadi tempat favorit
                            masyarakat lokal maupun wisatawan untuk bersantai dan menikmati keindahan alam.
                        </p>
                        <p>
                            Berdiri sebagai pusat rekreasi keluarga, kami berkomitmen untuk memberikan pengalaman berendam
                            air panas yang aman, nyaman, dan menyegarkan bagi seluruh pengunjung.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-linear-to-br from-sky-100 to-sky-200 p-8 text-center dark:from-sky-900/40 dark:to-sky-800/30">
                        <p class="text-4xl font-bold text-sky-700 dark:text-sky-400">100%</p>
                        <p class="mt-1 text-sm font-medium text-sky-600 dark:text-sky-400/80">Air Panas Alami</p>
                    </div>
                    <div class="rounded-2xl bg-linear-to-br from-green-100 to-green-200 p-8 text-center dark:from-green-900/40 dark:to-green-800/30">
                        <p class="text-4xl font-bold text-green-700 dark:text-green-400">7</p>
                        <p class="mt-1 text-sm font-medium text-green-600 dark:text-green-400/80">Hari Beroperasi</p>
                    </div>
                    <div class="rounded-2xl bg-linear-to-br from-amber-100 to-amber-200 p-8 text-center dark:from-amber-900/40 dark:to-amber-800/30">
                        <p class="text-4xl font-bold text-amber-700 dark:text-amber-400">24/7</p>
                        <p class="mt-1 text-sm font-medium text-amber-600 dark:text-amber-400/80">Keamanan</p>
                    </div>
                    <div class="rounded-2xl bg-linear-to-br from-violet-100 to-violet-200 p-8 text-center dark:from-violet-900/40 dark:to-violet-800/30">
                        <p class="text-4xl font-bold text-violet-700 dark:text-violet-400">5+</p>
                        <p class="mt-1 text-sm font-medium text-violet-600 dark:text-violet-400/80">Fasilitas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Visi & Misi -->
    <section class="bg-zinc-50 py-16 dark:bg-zinc-900 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <div class="rounded-2xl border border-sky-200 bg-linear-to-br from-sky-50 to-white p-8 dark:border-sky-900/50 dark:from-sky-900/20 dark:to-zinc-800 lg:p-10">
                    <div class="mb-4 flex size-14 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/30">
                        <flux:icon name="eye" class="size-7 text-sky-600 dark:text-sky-400" />
                    </div>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Visi</h3>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                        Menjadi destinasi wisata air panas terkemuka di Sulawesi Utara yang mengedepankan
                        kenyamanan, keamanan, dan kelestarian lingkungan.
                    </p>
                </div>
                <div class="rounded-2xl border border-green-200 bg-linear-to-br from-green-50 to-white p-8 dark:border-green-900/50 dark:from-green-900/20 dark:to-zinc-800 lg:p-10">
                    <div class="mb-4 flex size-14 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="rocket-launch" class="size-7 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Misi</h3>
                    <ul class="mt-4 space-y-2 text-zinc-600 dark:text-zinc-400">
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" variant="mini" class="mt-0.5 size-5 shrink-0 text-green-500" />
                            Menyediakan fasilitas kolam air panas alami yang bersih dan terawat
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" variant="mini" class="mt-0.5 size-5 shrink-0 text-green-500" />
                            Memberikan pelayanan terbaik kepada setiap pengunjung
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" variant="mini" class="mt-0.5 size-5 shrink-0 text-green-500" />
                            Menjaga kelestarian sumber air panas alami
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" variant="mini" class="mt-0.5 size-5 shrink-0 text-green-500" />
                            Mendukung perkembangan pariwisata daerah
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" variant="mini" class="mt-0.5 size-5 shrink-0 text-green-500" />
                            Menciptakan lapangan kerja bagi masyarakat sekitar
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Fasilitas -->
    <section class="relative bg-white py-16 dark:bg-zinc-800 sm:py-24">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-semibold uppercase tracking-wider text-sky-600 dark:text-sky-400">Fasilitas</span>
                <h2 class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">Fasilitas Lengkap untuk Kenyamanan Anda</h2>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 text-center transition hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/30">
                        <flux:icon name="fire" class="size-7 text-sky-600 dark:text-sky-400" />
                    </div>
                    <h4 class="font-semibold text-zinc-900 dark:text-white">Kolam Air Panas</h4>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Kolam air panas alami untuk dewasa dan anak-anak.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 text-center transition hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="home" class="size-7 text-green-600 dark:text-green-400" />
                    </div>
                    <h4 class="font-semibold text-zinc-900 dark:text-white">Gazebo & Istirahat</h4>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Area istirahat nyaman untuk bersantai bersama keluarga.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 text-center transition hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
                        <flux:icon name="shopping-bag" class="size-7 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h4 class="font-semibold text-zinc-900 dark:text-white">Kantin & Cafe</h4>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Berbagai pilihan makanan dan minuman tersedia.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 text-center transition hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30">
                        <flux:icon name="shield-check" class="size-7 text-violet-600 dark:text-violet-400" />
                    </div>
                    <h4 class="font-semibold text-zinc-900 dark:text-white">Keamanan 24 Jam</h4>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Pengawasan keamanan dan parkir yang aman.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Jam Operasional -->
    <section class="bg-zinc-50 py-16 dark:bg-zinc-900 sm:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-semibold uppercase tracking-wider text-sky-600 dark:text-sky-400">Jam Operasional</span>
                <h2 class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">Kapan Kami Buka?</h2>
            </div>
            <div class="mt-10 rounded-2xl border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-5 py-4 dark:bg-zinc-900">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/30">
                                <flux:icon name="clock" variant="mini" class="size-5 text-sky-600 dark:text-sky-400" />
                            </div>
                            <span class="font-medium text-zinc-900 dark:text-white">Setiap Hari</span>
                        </div>
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">08:00 - 22:00 WITA</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="relative overflow-hidden bg-linear-to-r from-sky-600 to-sky-800 py-16 dark:from-sky-800 dark:to-sky-950">
        <div class="animate-float-gentle absolute -left-10 -top-10 size-40 rounded-full bg-white/5 blur-2xl"></div>
        <div class="animate-float-slow absolute -bottom-10 -right-10 size-48 rounded-full bg-sky-300/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Tertarik Berkunjung?</h2>
            <p class="mt-3 text-sky-100">Pesan tiket online sekarang dan nikmati kemudahan masuk tanpa antri.</p>
            <div class="mt-6">
                <a href="{{ route('tiket.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-sky-700 shadow-sm hover:bg-sky-50">
                    <flux:icon name="ticket" variant="mini" class="size-5" />
                    Pesan Tiket
                </a>
            </div>
        </div>
    </section>
</div>

<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tentang Kami - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
}; ?>

<div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-sky-700 to-sky-900 py-20">
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="300" cy="200" r="180" fill="currentColor" class="text-white" />
                <circle cx="1100" cy="100" r="120" fill="currentColor" class="text-white" />
            </svg>
        </div>
        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-white sm:text-5xl">Tentang Kolam Po'opo</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-sky-100">
                Wisata kolam air panas alami terbaik di Sulawesi Utara
            </p>
        </div>
    </section>

    <!-- Content -->
    <section class="bg-white py-16 dark:bg-zinc-800">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="prose prose-zinc max-w-none dark:prose-invert">
                <h2>Sejarah Kami</h2>
                <p>
                    Kolam Po'opo merupakan destinasi wisata kolam air panas alami yang terletak di Sulawesi Utara.
                    Dengan sumber air panas alami yang kaya mineral, Kolam Po'opo telah menjadi tempat favorit
                    masyarakat lokal maupun wisatawan untuk bersantai dan menikmati keindahan alam.
                </p>
                <p>
                    Berdiri sebagai pusat rekreasi keluarga, kami berkomitmen untuk memberikan pengalaman berendam
                    air panas yang aman, nyaman, dan menyegarkan bagi seluruh pengunjung.
                </p>

                <h2>Visi & Misi</h2>
                <h3>Visi</h3>
                <p>
                    Menjadi destinasi wisata air panas terkemuka di Sulawesi Utara yang mengedepankan
                    kenyamanan, keamanan, dan kelestarian lingkungan.
                </p>

                <h3>Misi</h3>
                <ul>
                    <li>Menyediakan fasilitas kolam air panas alami yang bersih dan terawat</li>
                    <li>Memberikan pelayanan terbaik kepada setiap pengunjung</li>
                    <li>Menjaga kelestarian sumber air panas alami</li>
                    <li>Mendukung perkembangan pariwisata daerah</li>
                    <li>Menciptakan lapangan kerja bagi masyarakat sekitar</li>
                </ul>

                <h2>Fasilitas</h2>
                <div class="not-prose mt-6 grid gap-6 sm:grid-cols-2">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/30">
                            <flux:icon name="fire" class="size-6 text-sky-600 dark:text-sky-400" />
                        </div>
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Kolam Air Panas</h4>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Kolam air panas alami dengan suhu yang nyaman untuk dewasa dan anak-anak.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                            <flux:icon name="home" class="size-6 text-green-600 dark:text-green-400" />
                        </div>
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Gazebo & Area Istirahat</h4>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Area istirahat yang nyaman untuk bersantai bersama keluarga.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                            <flux:icon name="shopping-bag" class="size-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Kantin & Café</h4>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Berbagai pilihan makanan dan minuman untuk menemani waktu bersantai Anda.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                            <flux:icon name="shield-check" class="size-6 text-violet-600 dark:text-violet-400" />
                        </div>
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Keamanan 24 Jam</h4>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Area parkir yang aman dengan pengawasan keamanan sepanjang hari.</p>
                    </div>
                </div>

                <h2 class="mt-10">Jam Operasional</h2>
                <div class="not-prose">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Senin - Jumat</span>
                                <span class="text-zinc-600 dark:text-zinc-400">08:00 - 18:00 WITA</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Sabtu - Minggu</span>
                                <span class="text-zinc-600 dark:text-zinc-400">07:00 - 19:00 WITA</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Hari Libur Nasional</span>
                                <span class="text-zinc-600 dark:text-zinc-400">07:00 - 19:00 WITA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <h2 class="mt-10">Lokasi</h2>
                <p>
                    Kolam Po'opo berlokasi di Sulawesi Utara, Indonesia. Lokasi kami mudah dijangkau dari pusat kota
                    dan dikelilingi oleh keindahan alam tropis yang asri.
                </p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-sky-700 py-12 dark:bg-sky-900">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Tertarik Berkunjung?</h2>
            <p class="mt-3 text-sky-100">Pesan tiket online sekarang dan nikmati kemudahan masuk tanpa antri.</p>
            <div class="mt-6">
                <a href="{{ route('tiket.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-sky-700 hover:bg-sky-50">
                    <flux:icon name="ticket" variant="mini" class="size-5" />
                    Pesan Tiket
                </a>
            </div>
        </div>
    </section>
</div>

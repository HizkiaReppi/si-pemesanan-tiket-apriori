<?php

use App\Models\Gallery;
use App\Models\TicketType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kolam Po\'opo - Wisata Air Panas Alami')] #[Layout('layouts.guest')] class extends Component {
    public $ticketTypes;

    public $galleryItems;

    public function mount(): void
    {
        $this->ticketTypes = TicketType::active()->ordered()->with('prices')->get();
        $this->galleryItems = Gallery::active()->ordered()->take(8)->get();
    }
}; ?>

<div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-linear-to-br from-sky-600 via-sky-700 to-sky-900 dark:from-sky-800 dark:via-sky-900 dark:to-zinc-900">
        <!-- Animated background shapes -->
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 1440 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="200" cy="300" r="200" fill="currentColor" class="text-white"/>
                <circle cx="1200" cy="100" r="150" fill="currentColor" class="text-white"/>
                <circle cx="800" cy="500" r="180" fill="currentColor" class="text-white"/>
            </svg>
        </div>
        <!-- Animated floating orbs -->
        <div class="animate-float-gentle absolute left-10 top-20 size-40 rounded-full bg-white/5 blur-2xl"></div>
        <div class="animate-float-slow absolute bottom-20 right-20 size-56 rounded-full bg-sky-300/10 blur-3xl"></div>
        <div class="animate-float-reverse absolute right-1/3 top-1/4 size-32 rounded-full bg-cyan-200/10 blur-2xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8 lg:py-40">
            <div class="text-center">
                @php
                    $now = now();
                    $isOpen = $now->hour >= 8 && $now->hour < 22;
                @endphp
                <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm font-medium text-sky-100 backdrop-blur-sm">
                    @if($isOpen)
                        <span class="size-2 animate-pulse rounded-full bg-green-400"></span>
                        Buka Sekarang
                    @else
                        <span class="size-2 rounded-full bg-red-400"></span>
                        Tutup &middot; Buka pukul 08:00
                    @endif
                </span>
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Kolam Po'opo
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-sky-100 sm:text-xl">
                    Kolam renang air panas alami di Sulawesi Utara. Nikmati pengalaman berendam yang menyegarkan di tengah keindahan alam.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('tiket.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-sky-700 shadow-lg shadow-sky-900/30 transition hover:bg-sky-50 hover:shadow-xl">
                        <flux:icon name="ticket" variant="mini" class="size-5" />
                        Beli Tiket Sekarang
                    </a>
                    <a href="#info"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:border-white/50 hover:bg-white/10">
                        <flux:icon name="arrow-down" variant="mini" class="size-4" />
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </div>

        <!-- Wave divider -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 80L1440 80V20C1320 60 1080 0 720 40C360 80 120 20 0 50V80Z" class="fill-white/50 dark:fill-zinc-800/50"/>
                <path d="M0 80L1440 80V30C1200 60 960 10 600 45C240 80 60 30 0 55V80Z" class="fill-white dark:fill-zinc-800"/>
            </svg>
        </div>
    </section>

    <!-- Info Section -->
    <section id="info" class="relative bg-white py-16 dark:bg-zinc-800 sm:py-24">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-50 dark:opacity-30"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                    <flux:icon name="sparkles" variant="mini" class="size-3.5" />
                    Keunggulan Kami
                </span>
                <h2 class="mt-3 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                    Mengapa Kolam Po'opo?
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                    Tempat wisata air panas terbaik dengan fasilitas lengkap untuk keluarga.
                </p>
            </div>

            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="group rounded-2xl border border-zinc-200 bg-white/80 p-8 text-center shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900/80 dark:shadow-zinc-950/20">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-sky-100 transition-transform duration-300 group-hover:scale-110 dark:bg-sky-900/30">
                        <flux:icon name="fire" class="size-7 text-sky-600 dark:text-sky-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Air Panas Alami</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Sumber air panas alami yang bermanfaat untuk kesehatan kulit dan relaksasi tubuh.
                    </p>
                </div>

                <div class="group rounded-2xl border border-zinc-200 bg-white/80 p-8 text-center shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900/80 dark:shadow-zinc-950/20">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-green-100 transition-transform duration-300 group-hover:scale-110 dark:bg-green-900/30">
                        <flux:icon name="user-group" class="size-7 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Ramah Keluarga</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Kolam untuk dewasa dan anak-anak dengan pengawasan keamanan yang ketat.
                    </p>
                </div>

                <div class="group rounded-2xl border border-zinc-200 bg-white/80 p-8 text-center shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900/80 dark:shadow-zinc-950/20">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-amber-100 transition-transform duration-300 group-hover:scale-110 dark:bg-amber-900/30">
                        <flux:icon name="ticket" class="size-7 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Pesan Online</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Beli tiket secara online dengan mudah, pilih tanggal kunjungan, dan dapatkan e-tiket instan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Preview -->
    @if($ticketTypes->isNotEmpty())
        <section id="harga" class="relative bg-zinc-50 py-16 dark:bg-zinc-900 sm:py-24">
            <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-40 dark:opacity-20"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                        <flux:icon name="tag" variant="mini" class="size-3.5" />
                        Harga Terjangkau
                    </span>
                    <h2 class="mt-3 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                        Harga Tiket
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                        Harga terjangkau untuk pengalaman berendam yang tidak terlupakan.
                    </p>
                </div>

                <div class="mx-auto mt-12 grid max-w-4xl gap-6 sm:grid-cols-2 lg:grid-cols-{{ min($ticketTypes->count(), 3) }}">
                    @foreach($ticketTypes as $ticket)
                        @php
                            $weekdayPrice = $ticket->prices->firstWhere('day_type', \App\Enums\DayType::Weekday);
                            $weekendPrice = $ticket->prices->firstWhere('day_type', \App\Enums\DayType::Weekend);
                        @endphp
                        <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800 dark:shadow-zinc-950/20">
                            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $ticket->name }}</h3>
                            @if($ticket->description)
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $ticket->description }}</p>
                            @endif

                            <div class="mt-6 space-y-3">
                                @if($weekdayPrice)
                                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-900">
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Hari Kerja</span>
                                        <span class="text-lg font-bold text-zinc-900 dark:text-white">
                                            Rp {{ number_format($weekdayPrice->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                                @if($weekendPrice)
                                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-900">
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Akhir Pekan</span>
                                        <span class="text-lg font-bold text-zinc-900 dark:text-white">
                                            Rp {{ number_format($weekendPrice->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('tiket.index') }}" wire:navigate
                               class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-sky-700 hover:shadow-md">
                                <flux:icon name="shopping-cart" variant="mini" class="size-4" />
                                Beli Tiket
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Gallery Section -->
    @if($galleryItems->isNotEmpty())
        <section id="galeri" class="relative bg-zinc-50 py-16 dark:bg-zinc-900 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                        <flux:icon name="camera" variant="mini" class="size-3.5" />
                        Galeri
                    </span>
                    <h2 class="mt-3 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                        Galeri Foto
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                        Lihat suasana dan keindahan Kolam Po'opo.
                    </p>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($galleryItems as $photo)
                        <div class="group relative aspect-video overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($photo->image_path) }}"
                                alt="{{ $photo->title }}"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 flex items-end bg-linear-to-t from-black/60 to-transparent opacity-0 transition-opacity group-hover:opacity-100">
                                <div class="p-4">
                                    <p class="text-sm font-semibold text-white">{{ $photo->title }}</p>
                                    @if($photo->description)
                                        <p class="mt-1 text-xs text-white/80">{{ $photo->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- How It Works -->
    <section class="relative bg-white py-16 dark:bg-zinc-800 sm:py-24">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-50 dark:opacity-30"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                    <flux:icon name="numbered-list" variant="mini" class="size-3.5" />
                    Mudah & Cepat
                </span>
                <h2 class="mt-3 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                    Cara Memesan
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                    Pesan tiket dalam 3 langkah mudah.
                </p>
            </div>

            <div class="relative mt-12 grid gap-8 sm:grid-cols-3">
                <!-- Connecting line (hidden on mobile) -->
                <div class="absolute left-1/6 right-1/6 top-8 hidden h-0.5 bg-linear-to-r from-sky-200 via-sky-300 to-sky-200 dark:from-sky-800 dark:via-sky-700 dark:to-sky-800 sm:block"></div>

                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex size-16 items-center justify-center rounded-full bg-linear-to-br from-sky-500 to-sky-700 text-2xl font-bold text-white shadow-lg shadow-sky-500/30">
                        1
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Pilih Tiket</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Pilih tanggal kunjungan, jenis tiket, dan add-on yang diinginkan.
                    </p>
                </div>
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex size-16 items-center justify-center rounded-full bg-linear-to-br from-sky-500 to-sky-700 text-2xl font-bold text-white shadow-lg shadow-sky-500/30">
                        2
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Bayar</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Lakukan pembayaran melalui transfer bank atau metode pembayaran online.
                    </p>
                </div>
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex size-16 items-center justify-center rounded-full bg-linear-to-br from-sky-500 to-sky-700 text-2xl font-bold text-white shadow-lg shadow-sky-500/30">
                        3
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Kunjungi</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Tunjukkan e-tiket (QR Code) di gerbang masuk dan nikmati liburan Anda.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="relative overflow-hidden bg-linear-to-r from-sky-600 via-sky-700 to-sky-800 py-16 dark:from-sky-800 dark:via-sky-900 dark:to-zinc-900 sm:py-20">
        <!-- Animated decorations -->
        <div class="animate-float-gentle absolute -left-10 -top-10 size-40 rounded-full bg-white/5 blur-2xl"></div>
        <div class="animate-float-slow absolute -bottom-10 -right-10 size-48 rounded-full bg-sky-300/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">
                Siap untuk Berendam?
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-sky-100">
                Pesan tiket sekarang dan nikmati pengalaman berendam air panas alami yang menyegarkan bersama keluarga.
            </p>
            <div class="mt-8">
                <a href="{{ route('tiket.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-sm font-semibold text-sky-700 shadow-lg shadow-sky-900/30 transition hover:bg-sky-50 hover:shadow-xl">
                    <flux:icon name="ticket" variant="mini" class="size-5" />
                    Pesan Tiket Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Location & Map -->
    <section class="relative bg-zinc-50 py-16 dark:bg-zinc-900 sm:py-24">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-40 dark:opacity-20"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                    <flux:icon name="map-pin" variant="mini" class="size-3.5" />
                    Lokasi
                </span>
                <h2 class="mt-3 text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                    Lokasi Kami
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                    Temukan kami di Sulawesi Utara dan dapatkan petunjuk arah langsung ke lokasi.
                </p>
            </div>

            <div class="mt-12 grid gap-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/30">
                            <flux:icon name="map-pin" class="size-6 text-sky-600 dark:text-sky-400" />
                        </div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Alamat</h3>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Kolam Po'opo, Sulawesi Utara, Indonesia</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                            <flux:icon name="clock" class="size-6 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Jam Operasional</h3>
                        <div class="mt-1 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <p>Setiap Hari: 08:00 - 22:00 WITA</p>
                        </div>
                    </div>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=Kolam+Po'opo+Sulawesi+Utara" target="_blank" rel="noopener"
                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                        <flux:icon name="map-pin" variant="mini" class="size-5" />
                        Petunjuk Arah
                    </a>
                </div>
                <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700 lg:col-span-2">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.2!2d124.8!3d1.3!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sKolam+Po'opo!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid"
                        width="100%"
                        height="400"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        class="h-full min-h-87.5 w-full"
                    ></iframe>
                </div>
            </div>
        </div>
    </section>
</div>

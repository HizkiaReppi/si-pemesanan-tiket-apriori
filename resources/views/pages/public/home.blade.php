<?php

use App\Models\TicketType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kolam Po\'opo - Wisata Air Panas Alami')] #[Layout('layouts.guest')] class extends Component {
    public $ticketTypes;

    public function mount(): void
    {
        $this->ticketTypes = TicketType::active()->ordered()->with('prices')->get();
    }
}; ?>

<div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-sky-700 to-sky-900 dark:from-sky-800 dark:via-sky-900 dark:to-zinc-900">
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 1440 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="200" cy="300" r="200" fill="currentColor" class="text-white"/>
                <circle cx="1200" cy="100" r="150" fill="currentColor" class="text-white"/>
                <circle cx="800" cy="500" r="180" fill="currentColor" class="text-white"/>
            </svg>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8 lg:py-40">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Kolam Po'opo
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-sky-100 sm:text-xl">
                    Kolam renang air panas alami di Sulawesi Utara. Nikmati pengalaman berendam yang menyegarkan di tengah keindahan alam.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('tiket.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-sky-700 shadow-sm hover:bg-sky-50">
                        <flux:icon name="ticket" variant="mini" class="size-5" />
                        Beli Tiket Sekarang
                    </a>
                    <a href="#info"
                       class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 60L1440 60L1440 0C1200 50 240 50 0 0L0 60Z" class="fill-white dark:fill-zinc-800"/>
            </svg>
        </div>
    </section>

    <!-- Info Section -->
    <section id="info" class="bg-white py-16 dark:bg-zinc-800 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                    Mengapa Kolam Po'opo?
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                    Tempat wisata air panas terbaik dengan fasilitas lengkap untuk keluarga.
                </p>
            </div>

            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/30">
                        <flux:icon name="fire" class="size-7 text-sky-600 dark:text-sky-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Air Panas Alami</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Sumber air panas alami yang bermanfaat untuk kesehatan kulit dan relaksasi tubuh.
                    </p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="user-group" class="size-7 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Ramah Keluarga</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Kolam untuk dewasa dan anak-anak dengan pengawasan keamanan yang ketat.
                    </p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
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
        <section id="harga" class="bg-zinc-50 py-16 dark:bg-zinc-900 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
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
                        <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-800">
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
                               class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">
                                <flux:icon name="shopping-cart" variant="mini" class="size-4" />
                                Beli Tiket
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- How It Works -->
    <section class="bg-white py-16 dark:bg-zinc-800 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                    Cara Memesan
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                    Pesan tiket dalam 3 langkah mudah.
                </p>
            </div>

            <div class="mt-12 grid gap-8 sm:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-sky-100 text-2xl font-bold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                        1
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Pilih Tiket</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Pilih tanggal kunjungan, jenis tiket, dan add-on yang diinginkan.
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-sky-100 text-2xl font-bold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                        2
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Bayar</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Lakukan pembayaran melalui transfer bank atau metode pembayaran online.
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-sky-100 text-2xl font-bold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
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
    <section class="bg-sky-700 py-16 dark:bg-sky-900 sm:py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">
                Siap untuk Berendam?
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-sky-100">
                Pesan tiket sekarang dan nikmati pengalaman berendam air panas alami yang menyegarkan bersama keluarga.
            </p>
            <div class="mt-8">
                <a href="{{ route('tiket.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-8 py-3 text-sm font-semibold text-sky-700 shadow-sm hover:bg-sky-50">
                    <flux:icon name="ticket" variant="mini" class="size-5" />
                    Pesan Tiket Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Location & Map -->
    <section class="bg-white py-16 dark:bg-zinc-800 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                    Lokasi Kami
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                    Temukan kami di Sulawesi Utara dan dapatkan petunjuk arah langsung ke lokasi.
                </p>
            </div>

            <div class="mt-12 grid gap-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/30">
                            <flux:icon name="map-pin" class="size-6 text-sky-600 dark:text-sky-400" />
                        </div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Alamat</h3>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Kolam Po'opo, Sulawesi Utara, Indonesia</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                            <flux:icon name="clock" class="size-6 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Jam Operasional</h3>
                        <div class="mt-1 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <p>Senin - Jumat: 08:00 - 18:00 WITA</p>
                            <p>Sabtu - Minggu: 07:00 - 19:00 WITA</p>
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
                        class="h-full min-h-[350px] w-full"
                    ></iframe>
                </div>
            </div>
        </div>
    </section>
</div>

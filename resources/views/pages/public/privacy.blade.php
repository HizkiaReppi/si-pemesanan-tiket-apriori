<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kebijakan Privasi - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
}; ?>

<div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-linear-to-br from-sky-600 via-sky-700 to-sky-900 py-20">
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="300" cy="200" r="200" fill="currentColor" class="text-white"/>
                <circle cx="1100" cy="100" r="160" fill="currentColor" class="text-white"/>
                <circle cx="700" cy="350" r="120" fill="currentColor" class="text-white"/>
            </svg>
        </div>
        <!-- Animated floating orbs -->
        <div class="animate-float-gentle absolute -top-10 left-1/4 size-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="animate-float-slow absolute -bottom-10 right-1/3 size-52 rounded-full bg-sky-400/10 blur-2xl"></div>
        <div class="animate-float-reverse absolute left-10 top-16 size-24 rounded-full bg-sky-300/10 blur-xl"></div>
        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium text-sky-100 backdrop-blur-sm">
                <flux:icon name="shield-check" variant="mini" class="size-4" />
                Perlindungan Data Anda
            </span>
            <h1 class="mt-4 text-3xl font-bold text-white sm:text-4xl lg:text-5xl">Kebijakan Privasi</h1>
            <p class="mt-3 text-sky-100">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 60L1440 60L1440 0C1200 50 240 50 0 0L0 60Z" class="fill-zinc-50 dark:fill-zinc-900"/>
            </svg>
        </div>
    </section>

    <!-- Content -->
    <section class="relative bg-zinc-50 py-16 dark:bg-zinc-900">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-40"></div>
        <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                @php
                    $sections = [
                        ['title' => 'Pendahuluan', 'icon' => 'information-circle', 'color' => 'sky', 'content' => 'Kolam Po\'opo menghargai privasi Anda dan berkomitmen untuk melindungi data pribadi yang Anda berikan kepada kami. Kebijakan privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.'],
                        ['title' => 'Data yang Kami Kumpulkan', 'icon' => 'clipboard-document-list', 'color' => 'violet', 'intro' => 'Kami mengumpulkan data berikut saat Anda menggunakan layanan kami:', 'items' => [
                            '<strong>Data identitas:</strong> Nama lengkap yang Anda berikan saat pemesanan.',
                            '<strong>Data kontak:</strong> Alamat email dan nomor telepon.',
                            '<strong>Data transaksi:</strong> Riwayat pemesanan, pembayaran, dan penggunaan tiket.',
                            '<strong>Data akun:</strong> Informasi yang Anda berikan saat mendaftar akun.',
                            '<strong>Data teknis:</strong> Alamat IP, jenis browser, dan data penggunaan website.',
                        ]],
                        ['title' => 'Penggunaan Data', 'icon' => 'cog-6-tooth', 'color' => 'green', 'intro' => 'Data yang kami kumpulkan digunakan untuk:', 'items' => [
                            'Memproses dan mengelola pemesanan tiket Anda.',
                            'Mengirimkan konfirmasi pesanan, e-tiket, dan notifikasi terkait.',
                            'Mengirimkan informasi promosi dan penawaran khusus (dengan persetujuan Anda).',
                            'Meningkatkan kualitas layanan dan pengalaman pengguna.',
                            'Menganalisis tren kunjungan untuk perencanaan operasional.',
                            'Memenuhi kewajiban hukum dan regulasi.',
                        ]],
                        ['title' => 'Perlindungan Data', 'icon' => 'lock-closed', 'color' => 'amber', 'intro' => 'Kami menggunakan langkah-langkah keamanan yang wajar untuk melindungi data pribadi Anda, termasuk:', 'items' => [
                            'Enkripsi data sensitif menggunakan teknologi SSL/TLS.',
                            'Penyimpanan data yang aman dengan akses terbatas.',
                            'Autentikasi dua faktor (2FA) untuk keamanan akun.',
                            'Audit keamanan berkala untuk memastikan integritas sistem.',
                        ]],
                        ['title' => 'Pembagian Data', 'icon' => 'share', 'color' => 'red', 'intro' => 'Kami tidak menjual, memperdagangkan, atau menyewakan data pribadi Anda kepada pihak ketiga. Data Anda hanya dibagikan kepada:', 'items' => [
                            '<strong>Penyedia layanan pembayaran:</strong> Untuk memproses transaksi pembayaran secara aman.',
                            '<strong>Penyedia layanan email:</strong> Untuk mengirimkan notifikasi dan e-tiket.',
                            '<strong>Otoritas hukum:</strong> Jika diwajibkan oleh hukum atau proses hukum yang berlaku.',
                        ]],
                        ['title' => 'Hak Anda', 'icon' => 'user-circle', 'color' => 'sky', 'intro' => 'Anda memiliki hak untuk:', 'items' => [
                            'Mengakses data pribadi yang kami simpan tentang Anda.',
                            'Meminta koreksi data yang tidak akurat.',
                            'Meminta penghapusan data pribadi Anda (dengan batasan tertentu).',
                            'Menolak penggunaan data untuk pemasaran langsung.',
                            'Meminta salinan data pribadi Anda dalam format yang dapat dibaca mesin.',
                        ]],
                        ['title' => 'Cookie', 'icon' => 'globe-alt', 'color' => 'green', 'content' => 'Website kami menggunakan cookie untuk meningkatkan pengalaman pengguna. Cookie digunakan untuk menyimpan preferensi, keranjang belanja, dan sesi login. Anda dapat mengelola pengaturan cookie melalui browser Anda.'],
                        ['title' => 'Retensi Data', 'icon' => 'archive-box', 'color' => 'amber', 'content' => 'Kami menyimpan data pribadi Anda selama diperlukan untuk tujuan yang dijelaskan dalam kebijakan ini, atau selama diwajibkan oleh hukum. Data transaksi disimpan selama minimal 5 tahun untuk keperluan akuntansi dan pajak.'],
                        ['title' => 'Perubahan Kebijakan', 'icon' => 'arrow-path', 'color' => 'violet', 'content' => 'Kami dapat memperbarui kebijakan privasi ini dari waktu ke waktu. Perubahan akan dipublikasikan di halaman ini dengan tanggal pembaruan terbaru. Kami menyarankan Anda untuk meninjau kebijakan ini secara berkala.'],
                    ];
                @endphp

                @foreach($sections as $i => $section)
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 sm:p-8">
                        <div class="flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-{{ $section['color'] }}-100 dark:bg-{{ $section['color'] }}-900/30">
                                <flux:icon :name="$section['icon']" class="size-6 text-{{ $section['color'] }}-600 dark:text-{{ $section['color'] }}-400" />
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">{{ $i + 1 }}. {{ $section['title'] }}</h2>
                                @if(isset($section['content']))
                                    <p class="mt-3 text-zinc-600 dark:text-zinc-400">{{ $section['content'] }}</p>
                                @endif
                                @if(isset($section['intro']))
                                    <p class="mt-3 text-zinc-600 dark:text-zinc-400">{{ $section['intro'] }}</p>
                                @endif
                                @if(isset($section['items']))
                                    <ul class="mt-3 space-y-2">
                                        @foreach($section['items'] as $item)
                                            <li class="flex items-start gap-2 text-zinc-600 dark:text-zinc-400">
                                                <flux:icon name="check" variant="mini" class="mt-0.5 size-4 shrink-0 text-{{ $section['color'] }}-500" />
                                                <span>{!! $item !!}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Contact Section --}}
                <div class="rounded-2xl border border-sky-200 bg-linear-to-r from-sky-50 to-sky-100 p-6 dark:border-sky-900/50 dark:from-sky-900/20 dark:to-sky-800/10 sm:p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-200 dark:bg-sky-900/50">
                            <flux:icon name="envelope" class="size-6 text-sky-700 dark:text-sky-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">10. Hubungi Kami</h2>
                            <p class="mt-3 text-zinc-600 dark:text-zinc-400">
                                Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami di
                                <a href="mailto:privasi@kolampoopo.id" class="font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400">privasi@kolampoopo.id</a> atau melalui
                                <a href="{{ route('kontak') }}" wire:navigate class="font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400">halaman kontak</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

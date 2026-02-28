<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('FAQ - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
    public array $faqs = [
        [
            'question' => 'Bagaimana cara memesan tiket online?',
            'answer' => 'Anda dapat memesan tiket melalui halaman Tiket di website kami. Pilih tanggal kunjungan, jenis tiket, dan add-on yang diinginkan, lalu lanjutkan ke pembayaran. Setelah pembayaran dikonfirmasi, E-Tiket (QR Code) akan dikirimkan ke email Anda.',
        ],
        [
            'question' => 'Metode pembayaran apa saja yang tersedia?',
            'answer' => 'Kami menyediakan dua metode pembayaran: Transfer Bank Manual (BRI) dan Pembayaran Instan (QRIS, Virtual Account, e-Wallet). Untuk Transfer Bank, Anda perlu mengunggah bukti transfer untuk diverifikasi oleh admin.',
        ],
        [
            'question' => 'Berapa lama batas waktu pembayaran?',
            'answer' => 'Anda memiliki waktu 2 jam setelah pemesanan untuk menyelesaikan pembayaran. Jika melewati batas waktu tersebut, pesanan akan secara otomatis dibatalkan.',
        ],
        [
            'question' => 'Apakah tiket bisa dibatalkan atau direfund?',
            'answer' => 'Pembatalan hanya dapat dilakukan sebelum pembayaran diverifikasi. Tiket yang sudah dikonfirmasi tidak dapat dibatalkan. Namun, Anda dapat mengubah tanggal kunjungan maksimal 24 jam sebelum tanggal yang sudah dipilih.',
        ],
        [
            'question' => 'Bagaimana cara menggunakan E-Tiket?',
            'answer' => 'Tunjukkan QR Code yang ada di E-Tiket (melalui email atau halaman pesanan) kepada petugas di pintu masuk. QR Code hanya berlaku untuk satu kali masuk pada tanggal yang tertera.',
        ],
        [
            'question' => 'Apakah ada batasan usia untuk kolam air panas?',
            'answer' => 'Tidak ada batasan usia khusus, namun anak-anak di bawah 12 tahun wajib didampingi oleh orang dewasa. Kami menyediakan tiket Dewasa dan Anak dengan harga berbeda.',
        ],
        [
            'question' => 'Berapa jam operasional Kolam Po\'opo?',
            'answer' => 'Setiap hari: 08:00 - 22:00 WITA.',
        ],
        [
            'question' => 'Apakah boleh membawa makanan dari luar?',
            'answer' => 'Demi kenyamanan bersama, makanan dan minuman dari luar tidak diperbolehkan (kecuali air mineral). Kami menyediakan kantin dengan berbagai pilihan makanan dan minuman.',
        ],
        [
            'question' => 'Apakah ada fasilitas parkir?',
            'answer' => 'Ya, kami menyediakan area parkir yang luas dan aman dengan pengawasan keamanan. Parkir kendaraan roda dua dan roda empat tersedia.',
        ],
        [
            'question' => 'Bagaimana cara menggunakan voucher diskon?',
            'answer' => 'Saat checkout, masukkan kode voucher pada kolom yang tersedia dan klik "Terapkan". Diskon akan langsung dikalkulasi dan ditampilkan pada ringkasan pesanan Anda. Voucher memiliki syarat dan ketentuan tertentu yang perlu diperhatikan.',
        ],
    ];
}; ?>

<div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-linear-to-br from-sky-600 via-sky-700 to-sky-900 py-16">
        <!-- Animated floating orbs -->
        <div class="animate-float-gentle absolute -top-10 left-1/4 size-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="animate-float-slow absolute -bottom-10 right-1/4 size-52 rounded-full bg-sky-400/10 blur-2xl"></div>
        <div class="animate-float-delayed absolute right-10 top-10 size-24 rounded-full bg-sky-300/10 blur-xl"></div>

        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-sky-100 backdrop-blur-sm">
                <flux:icon name="question-mark-circle" variant="mini" class="size-4" />
                Pusat Bantuan
            </span>
            <h1 class="mt-4 text-3xl font-bold text-white sm:text-4xl">Pertanyaan yang Sering Diajukan</h1>
            <p class="mt-3 text-sky-100">Temukan jawaban untuk pertanyaan umum seputar Kolam Po'opo</p>
        </div>

        <!-- Wave divider -->
        <div class="absolute -bottom-1 left-0 w-full">
            <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0,40 C360,100 1080,0 1440,60 L1440,100 L0,100Z" class="fill-white dark:fill-zinc-800" />
            </svg>
        </div>
    </section>

    <!-- FAQ List -->
    <section class="relative bg-white py-16 dark:bg-zinc-800">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-40"></div>
        <div class="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                @foreach($faqs as $index => $faq)
                    <details class="group rounded-xl border border-zinc-200 bg-white shadow-sm transition-all duration-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                        <summary class="flex cursor-pointer items-center justify-between px-6 py-4">
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $faq['question'] }}</span>
                            <flux:icon name="chevron-down" variant="mini" class="size-5 text-zinc-400 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="border-t border-zinc-100 px-6 py-4 dark:border-zinc-800">
                            <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $faq['answer'] }}</p>
                        </div>
                    </details>
                @endforeach
            </div>

            <!-- Still have questions? -->
            <div class="mt-12 rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Masih ada pertanyaan?</h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Jangan ragu untuk menghubungi kami. Tim kami siap membantu Anda.
                </p>
                <div class="mt-4">
                    <a href="{{ route('kontak') }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sky-900/30 transition hover:bg-sky-700">
                        <flux:icon name="envelope" variant="mini" class="size-4" />
                        Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

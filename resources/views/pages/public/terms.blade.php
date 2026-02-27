<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Syarat & Ketentuan - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
}; ?>

<div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-sky-700 to-sky-900 py-20">
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="200" cy="150" r="180" fill="currentColor" class="text-white"/>
                <circle cx="900" cy="300" r="220" fill="currentColor" class="text-white"/>
                <circle cx="1300" cy="80" r="140" fill="currentColor" class="text-white"/>
            </svg>
        </div>
        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium text-sky-100 backdrop-blur-sm">
                <flux:icon name="document-text" variant="mini" class="size-4" />
                Dokumen Legal
            </span>
            <h1 class="mt-4 text-3xl font-bold text-white sm:text-4xl lg:text-5xl">Syarat & Ketentuan</h1>
            <p class="mt-3 text-sky-100">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 60L1440 60L1440 0C1200 50 240 50 0 0L0 60Z" class="fill-zinc-50 dark:fill-zinc-900"/>
            </svg>
        </div>
    </section>

    <!-- Content -->
    <section class="bg-zinc-50 py-16 dark:bg-zinc-900">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                @php
                    $sections = [
                        ['title' => 'Ketentuan Umum', 'icon' => 'information-circle', 'color' => 'sky', 'content' => 'Dengan menggunakan layanan Kolam Po\'opo, baik melalui website maupun secara langsung, Anda menyetujui dan terikat oleh syarat dan ketentuan berikut ini. Kolam Po\'opo berhak mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya.'],
                        ['title' => 'Pemesanan Tiket', 'icon' => 'ticket', 'color' => 'green', 'items' => ['Tiket dapat dipesan secara online melalui website resmi Kolam Po\'opo atau secara langsung di loket.', 'Pemesanan online memerlukan data diri yang valid termasuk nama, email, dan nomor telepon.', 'Tiket yang sudah dipesan berlaku untuk tanggal kunjungan yang telah dipilih.', 'Harga tiket dapat berbeda antara hari kerja (Senin-Jumat) dan akhir pekan (Sabtu-Minggu).', 'Setiap pemesanan akan mendapatkan kode pesanan unik yang digunakan sebagai referensi.']],
                        ['title' => 'Pembayaran', 'icon' => 'credit-card', 'color' => 'amber', 'items' => ['Pembayaran harus diselesaikan dalam waktu 2 jam setelah pemesanan. Pesanan akan otomatis dibatalkan jika melewati batas waktu.', 'Metode pembayaran yang tersedia: Transfer Bank Manual dan Pembayaran Instan (QRIS, VA, e-Wallet).', 'Untuk pembayaran Transfer Bank, bukti transfer harus diunggah dan akan diverifikasi oleh admin dalam waktu maksimal 1×24 jam.', 'Harga yang tertera sudah termasuk pajak dan biaya layanan, kecuali disebutkan lain.']],
                        ['title' => 'Pembatalan & Pengembalian Dana', 'icon' => 'arrow-uturn-left', 'color' => 'red', 'items' => ['Pembatalan pesanan hanya dapat dilakukan sebelum pembayaran diverifikasi.', 'Tiket yang sudah dibayar dan dikonfirmasi tidak dapat dibatalkan atau dikembalikan dananya.', 'Perubahan tanggal kunjungan dapat dilakukan maksimal 24 jam sebelum tanggal kunjungan yang telah dipilih.', 'Kolam Po\'opo berhak membatalkan tiket dan mengembalikan dana penuh jika terjadi force majeure.']],
                        ['title' => 'Penggunaan Tiket', 'icon' => 'qr-code', 'color' => 'violet', 'items' => ['Tiket hanya berlaku untuk satu kali masuk pada tanggal yang tertera.', 'E-Tiket (QR Code) harus ditunjukkan kepada petugas di pintu masuk.', 'Tiket tidak dapat dipindahtangankan tanpa persetujuan dari Kolam Po\'opo.', 'Kolam Po\'opo berhak menolak tiket yang dicurigai palsu atau dipalsukan.']],
                        ['title' => 'Tata Tertib Pengunjung', 'icon' => 'shield-check', 'color' => 'sky', 'items' => ['Pengunjung wajib menjaga kebersihan dan ketertiban di area Kolam Po\'opo.', 'Anak-anak di bawah 12 tahun wajib didampingi oleh orang dewasa.', 'Dilarang membawa makanan dan minuman dari luar area (kecuali air mineral).', 'Dilarang merokok di area kolam renang.', 'Pengunjung wajib mematuhi instruksi petugas keamanan dan lifeguard.', 'Kolam Po\'opo tidak bertanggung jawab atas kehilangan barang berharga pengunjung.']],
                        ['title' => 'Voucher & Promosi', 'icon' => 'tag', 'color' => 'green', 'items' => ['Voucher tidak dapat digabung dengan promosi lainnya kecuali disebutkan secara khusus.', 'Voucher memiliki masa berlaku yang tertera dan tidak dapat diperpanjang.', 'Kolam Po\'opo berhak membatalkan voucher yang menunjukkan indikasi penyalahgunaan.']],
                        ['title' => 'Batasan Tanggung Jawab', 'icon' => 'exclamation-triangle', 'color' => 'amber', 'content' => 'Kolam Po\'opo tidak bertanggung jawab atas kerugian langsung maupun tidak langsung yang timbul akibat penggunaan layanan kami, termasuk namun tidak terbatas pada gangguan teknis, kesalahan sistem, atau force majeure.'],
                        ['title' => 'Hukum yang Berlaku', 'icon' => 'scale', 'color' => 'violet', 'content' => 'Syarat dan ketentuan ini tunduk pada hukum yang berlaku di Republik Indonesia. Segala perselisihan akan diselesaikan secara musyawarah terlebih dahulu.'],
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
                                @if(isset($section['items']))
                                    <ul class="mt-3 space-y-2">
                                        @foreach($section['items'] as $item)
                                            <li class="flex items-start gap-2 text-zinc-600 dark:text-zinc-400">
                                                <flux:icon name="check" variant="mini" class="mt-0.5 size-4 shrink-0 text-{{ $section['color'] }}-500" />
                                                <span>{{ $item }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Contact Section --}}
                <div class="rounded-2xl border border-sky-200 bg-gradient-to-r from-sky-50 to-sky-100 p-6 dark:border-sky-900/50 dark:from-sky-900/20 dark:to-sky-800/10 sm:p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-200 dark:bg-sky-900/50">
                            <flux:icon name="envelope" class="size-6 text-sky-700 dark:text-sky-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">10. Kontak</h2>
                            <p class="mt-3 text-zinc-600 dark:text-zinc-400">
                                Untuk pertanyaan mengenai syarat dan ketentuan ini, silakan hubungi kami melalui
                                <a href="{{ route('kontak') }}" wire:navigate class="font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400">halaman kontak</a> atau email di
                                <a href="mailto:info@kolampoopo.id" class="font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400">info@kolampoopo.id</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

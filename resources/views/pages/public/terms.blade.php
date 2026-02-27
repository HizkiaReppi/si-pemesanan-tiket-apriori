<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Syarat & Ketentuan - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
}; ?>

<div>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-sky-600 via-sky-700 to-sky-900 py-16">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Syarat & Ketentuan</h1>
            <p class="mt-3 text-sky-100">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
        </div>
    </section>

    <!-- Content -->
    <section class="bg-white py-16 dark:bg-zinc-800">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="prose prose-zinc max-w-none dark:prose-invert">
                <h2>1. Ketentuan Umum</h2>
                <p>
                    Dengan menggunakan layanan Kolam Po'opo, baik melalui website maupun secara langsung,
                    Anda menyetujui dan terikat oleh syarat dan ketentuan berikut ini. Kolam Po'opo berhak
                    mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya.
                </p>

                <h2>2. Pemesanan Tiket</h2>
                <ul>
                    <li>Tiket dapat dipesan secara online melalui website resmi Kolam Po'opo atau secara langsung di loket.</li>
                    <li>Pemesanan online memerlukan data diri yang valid termasuk nama, email, dan nomor telepon.</li>
                    <li>Tiket yang sudah dipesan berlaku untuk tanggal kunjungan yang telah dipilih.</li>
                    <li>Harga tiket dapat berbeda antara hari kerja (Senin-Jumat) dan akhir pekan (Sabtu-Minggu).</li>
                    <li>Setiap pemesanan akan mendapatkan kode pesanan unik yang digunakan sebagai referensi.</li>
                </ul>

                <h2>3. Pembayaran</h2>
                <ul>
                    <li>Pembayaran harus diselesaikan dalam waktu 2 jam setelah pemesanan. Pesanan akan otomatis dibatalkan jika melewati batas waktu.</li>
                    <li>Metode pembayaran yang tersedia: Transfer Bank Manual dan Pembayaran Instan (QRIS, VA, e-Wallet).</li>
                    <li>Untuk pembayaran Transfer Bank, bukti transfer harus diunggah dan akan diverifikasi oleh admin dalam waktu maksimal 1×24 jam.</li>
                    <li>Harga yang tertera sudah termasuk pajak dan biaya layanan, kecuali disebutkan lain.</li>
                </ul>

                <h2>4. Pembatalan & Pengembalian Dana</h2>
                <ul>
                    <li>Pembatalan pesanan hanya dapat dilakukan sebelum pembayaran diverifikasi.</li>
                    <li>Tiket yang sudah dibayar dan dikonfirmasi tidak dapat dibatalkan atau dikembalikan dananya.</li>
                    <li>Perubahan tanggal kunjungan dapat dilakukan maksimal 24 jam sebelum tanggal kunjungan yang telah dipilih.</li>
                    <li>Kolam Po'opo berhak membatalkan tiket dan mengembalikan dana penuh jika terjadi force majeure.</li>
                </ul>

                <h2>5. Penggunaan Tiket</h2>
                <ul>
                    <li>Tiket hanya berlaku untuk satu kali masuk pada tanggal yang tertera.</li>
                    <li>E-Tiket (QR Code) harus ditunjukkan kepada petugas di pintu masuk.</li>
                    <li>Tiket tidak dapat dipindahtangankan tanpa persetujuan dari Kolam Po'opo.</li>
                    <li>Kolam Po'opo berhak menolak tiket yang dicurigai palsu atau dipalsukan.</li>
                </ul>

                <h2>6. Tata Tertib Pengunjung</h2>
                <ul>
                    <li>Pengunjung wajib menjaga kebersihan dan ketertiban di area Kolam Po'opo.</li>
                    <li>Anak-anak di bawah 12 tahun wajib didampingi oleh orang dewasa.</li>
                    <li>Dilarang membawa makanan dan minuman dari luar area (kecuali air mineral).</li>
                    <li>Dilarang merokok di area kolam renang.</li>
                    <li>Pengunjung wajib mematuhi instruksi petugas keamanan dan lifeguard.</li>
                    <li>Kolam Po'opo tidak bertanggung jawab atas kehilangan barang berharga pengunjung.</li>
                </ul>

                <h2>7. Voucher & Promosi</h2>
                <ul>
                    <li>Voucher tidak dapat digabung dengan promosi lainnya kecuali disebutkan secara khusus.</li>
                    <li>Voucher memiliki masa berlaku yang tertera dan tidak dapat diperpanjang.</li>
                    <li>Kolam Po'opo berhak membatalkan voucher yang menunjukkan indikasi penyalahgunaan.</li>
                </ul>

                <h2>8. Batasan Tanggung Jawab</h2>
                <p>
                    Kolam Po'opo tidak bertanggung jawab atas kerugian langsung maupun tidak langsung yang timbul
                    akibat penggunaan layanan kami, termasuk namun tidak terbatas pada gangguan teknis, kesalahan
                    sistem, atau force majeure.
                </p>

                <h2>9. Hukum yang Berlaku</h2>
                <p>
                    Syarat dan ketentuan ini tunduk pada hukum yang berlaku di Republik Indonesia.
                    Segala perselisihan akan diselesaikan secara musyawarah terlebih dahulu.
                </p>

                <h2>10. Kontak</h2>
                <p>
                    Untuk pertanyaan mengenai syarat dan ketentuan ini, silakan hubungi kami melalui
                    <a href="{{ route('kontak') }}">halaman kontak</a> atau email di
                    <a href="mailto:info@kolampoopo.id">info@kolampoopo.id</a>.
                </p>
            </div>
        </div>
    </section>
</div>

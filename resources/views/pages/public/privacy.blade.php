<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kebijakan Privasi - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
}; ?>

<div>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-sky-600 via-sky-700 to-sky-900 py-16">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Kebijakan Privasi</h1>
            <p class="mt-3 text-sky-100">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
        </div>
    </section>

    <!-- Content -->
    <section class="bg-white py-16 dark:bg-zinc-800">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="prose prose-zinc max-w-none dark:prose-invert">
                <h2>1. Pendahuluan</h2>
                <p>
                    Kolam Po'opo menghargai privasi Anda dan berkomitmen untuk melindungi data pribadi
                    yang Anda berikan kepada kami. Kebijakan privasi ini menjelaskan bagaimana kami
                    mengumpulkan, menggunakan, dan melindungi informasi Anda.
                </p>

                <h2>2. Data yang Kami Kumpulkan</h2>
                <p>Kami mengumpulkan data berikut saat Anda menggunakan layanan kami:</p>
                <ul>
                    <li><strong>Data identitas:</strong> Nama lengkap yang Anda berikan saat pemesanan.</li>
                    <li><strong>Data kontak:</strong> Alamat email dan nomor telepon.</li>
                    <li><strong>Data transaksi:</strong> Riwayat pemesanan, pembayaran, dan penggunaan tiket.</li>
                    <li><strong>Data akun:</strong> Informasi yang Anda berikan saat mendaftar akun.</li>
                    <li><strong>Data teknis:</strong> Alamat IP, jenis browser, dan data penggunaan website.</li>
                </ul>

                <h2>3. Penggunaan Data</h2>
                <p>Data yang kami kumpulkan digunakan untuk:</p>
                <ul>
                    <li>Memproses dan mengelola pemesanan tiket Anda.</li>
                    <li>Mengirimkan konfirmasi pesanan, e-tiket, dan notifikasi terkait.</li>
                    <li>Mengirimkan informasi promosi dan penawaran khusus (dengan persetujuan Anda).</li>
                    <li>Meningkatkan kualitas layanan dan pengalaman pengguna.</li>
                    <li>Menganalisis tren kunjungan untuk perencanaan operasional.</li>
                    <li>Memenuhi kewajiban hukum dan regulasi.</li>
                </ul>

                <h2>4. Perlindungan Data</h2>
                <p>
                    Kami menggunakan langkah-langkah keamanan yang wajar untuk melindungi data pribadi Anda,
                    termasuk:
                </p>
                <ul>
                    <li>Enkripsi data sensitif menggunakan teknologi SSL/TLS.</li>
                    <li>Penyimpanan data yang aman dengan akses terbatas.</li>
                    <li>Autentikasi dua faktor (2FA) untuk keamanan akun.</li>
                    <li>Audit keamanan berkala untuk memastikan integritas sistem.</li>
                </ul>

                <h2>5. Pembagian Data</h2>
                <p>
                    Kami tidak menjual, memperdagangkan, atau menyewakan data pribadi Anda kepada pihak ketiga.
                    Data Anda hanya dibagikan kepada:
                </p>
                <ul>
                    <li><strong>Penyedia layanan pembayaran:</strong> Untuk memproses transaksi pembayaran secara aman.</li>
                    <li><strong>Penyedia layanan email:</strong> Untuk mengirimkan notifikasi dan e-tiket.</li>
                    <li><strong>Otoritas hukum:</strong> Jika diwajibkan oleh hukum atau proses hukum yang berlaku.</li>
                </ul>

                <h2>6. Hak Anda</h2>
                <p>Anda memiliki hak untuk:</p>
                <ul>
                    <li>Mengakses data pribadi yang kami simpan tentang Anda.</li>
                    <li>Meminta koreksi data yang tidak akurat.</li>
                    <li>Meminta penghapusan data pribadi Anda (dengan batasan tertentu).</li>
                    <li>Menolak penggunaan data untuk pemasaran langsung.</li>
                    <li>Meminta salinan data pribadi Anda dalam format yang dapat dibaca mesin.</li>
                </ul>

                <h2>7. Cookie</h2>
                <p>
                    Website kami menggunakan cookie untuk meningkatkan pengalaman pengguna.
                    Cookie digunakan untuk menyimpan preferensi, keranjang belanja, dan sesi login.
                    Anda dapat mengelola pengaturan cookie melalui browser Anda.
                </p>

                <h2>8. Retensi Data</h2>
                <p>
                    Kami menyimpan data pribadi Anda selama diperlukan untuk tujuan yang dijelaskan
                    dalam kebijakan ini, atau selama diwajibkan oleh hukum. Data transaksi disimpan
                    selama minimal 5 tahun untuk keperluan akuntansi dan pajak.
                </p>

                <h2>9. Perubahan Kebijakan</h2>
                <p>
                    Kami dapat memperbarui kebijakan privasi ini dari waktu ke waktu. Perubahan akan
                    dipublikasikan di halaman ini dengan tanggal pembaruan terbaru. Kami menyarankan
                    Anda untuk meninjau kebijakan ini secara berkala.
                </p>

                <h2>10. Hubungi Kami</h2>
                <p>
                    Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami di
                    <a href="mailto:privasi@kolampoopo.id">privasi@kolampoopo.id</a> atau melalui
                    <a href="{{ route('kontak') }}">halaman kontak</a>.
                </p>
            </div>
        </div>
    </section>
</div>

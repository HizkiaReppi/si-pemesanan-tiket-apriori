<x-mail.layout :subject="'E-Tiket - ' . $order->code" headerSubtitle="E-Tiket Kunjungan Anda">
    <h2>Halo, {{ $order->customer_name }}! 👋</h2>
    <p>Berikut adalah e-tiket untuk kunjungan Anda ke <strong>Kolam Po'opo</strong>.</p>

    <div class="info-card">
        <div class="info-row">
            <span class="info-label">Kode Pesanan</span>
            <span class="info-value">{{ $order->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Kunjungan</span>
            <span class="info-value">{{ $order->visit_date->translatedFormat('l, d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipe Hari</span>
            <span class="info-value">{{ $order->day_type->label() }}</span>
        </div>
    </div>

    <h2>Detail Tiket</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($qrSvg)
        <h2>QR Code E-Tiket</h2>
        <div class="qr-container">
            @if(isset($qrImageCid))
                <img src="{{ $qrImageCid }}" width="200" height="200" alt="QR Code E-Tiket" style="display:inline-block;">
            @else
                {!! $qrSvg !!}
            @endif
            <p class="text-muted" style="margin-top: 12px;">Tunjukkan QR code ini di pintu masuk</p>
        </div>
    @endif

    <h2>Petunjuk Penggunaan</h2>
    <ol>
        <li>Tunjukkan QR Code atau kode pesanan <strong>{{ $order->code }}</strong> di pintu masuk</li>
        <li>Pastikan datang sesuai tanggal kunjungan</li>
        <li>QR Code hanya berlaku untuk satu kali masuk</li>
        <li>Jam operasional: <strong>08:00 - 18:00 WITA</strong></li>
    </ol>

    <div class="btn-block">
        <a href="{{ route('pesanan.detail', $order) }}" class="btn">Lihat E-Tiket</a>
    </div>

    <div class="alert-info">
        💡 Simpan email ini sebagai bukti tiket Anda. Jika mengalami kendala, hubungi kami melalui halaman kontak.
    </div>

    <p>Selamat menikmati kunjungan Anda! 🎉</p>
</x-mail.layout>

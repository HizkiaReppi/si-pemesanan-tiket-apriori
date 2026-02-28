<x-mail.layout :subject="'Pembayaran Dikonfirmasi - ' . $order->code" headerSubtitle="Konfirmasi Pembayaran">
    <h2>Halo, {{ $order->customer_name }}! ✅</h2>
    <p>Pembayaran untuk pesanan Anda telah <strong>berhasil dikonfirmasi</strong>. Berikut ringkasan pesanan Anda:</p>

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
            <span class="info-label">Total Dibayar</span>
            <span class="info-value">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <h2>Detail Item</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Jumlah</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="divider">

    <div class="alert-success">
        🎫 E-Tiket Anda akan segera dikirim dalam email terpisah. Silakan tunjukkan QR Code di pintu masuk pada tanggal kunjungan.
    </div>

    <div class="btn-block">
        <a href="{{ route('pesanan.detail', $order) }}" class="btn">Lihat Detail Pesanan</a>
    </div>

    <p class="text-muted">Salam hangat,<br>Tim Kolam Po'opo</p>
</x-mail.layout>

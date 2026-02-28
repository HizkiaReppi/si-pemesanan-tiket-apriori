<x-mail.layout :subject="'Kunjungan Selesai - ' . $order->code" headerSubtitle="Terima Kasih Atas Kunjungan Anda">
    <h2>Halo, {{ $order->customer_name }}! 🙏</h2>
    <p>Terima kasih telah mengunjungi <strong>Kolam Po'opo</strong>! Kami harap Anda menikmati pengalaman berendam air panas alami bersama kami.</p>

    <div class="info-card">
        <div class="info-row">
            <span class="info-label">Kode Pesanan</span>
            <span class="info-value">{{ $order->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Kunjungan</span>
            <span class="info-value">{{ $order->visit_date->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    <h2>Ringkasan Kunjungan</h2>
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
            <tr class="total-row">
                <td colspan="2"><strong>Total</strong></td>
                <td style="text-align: right;"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <hr class="divider">

    <p>Kami sangat senang Anda memilih Kolam Po'opo. Jangan ragu untuk berkunjung kembali! 🏊</p>

    <div class="btn-block">
        <a href="{{ route('tiket.index') }}" class="btn">Pesan Tiket Lagi</a>
    </div>

    <p class="text-muted">Salam hangat,<br>Tim Kolam Po'opo</p>
</x-mail.layout>

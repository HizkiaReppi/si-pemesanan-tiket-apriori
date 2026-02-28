<x-mail.layout :subject="'Pesanan Dibuat - ' . $order->code" headerSubtitle="Konfirmasi Pesanan">
    <h2>Halo, {{ $order->customer_name }}! 👋</h2>
    <p>Terima kasih telah memesan tiket di <strong>Kolam Po'opo</strong>! Berikut adalah detail pesanan Anda:</p>

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
            @if($order->discount > 0)
                <tr class="discount-row">
                    <td colspan="2">Diskon</td>
                    <td style="text-align: right;">-Rp {{ number_format($order->discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="2"><strong>Total Pembayaran</strong></td>
                <td style="text-align: right;"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <h2>Metode Pembayaran: {{ $order->payment_method->label() }}</h2>

    @if($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
        <p>Silakan transfer ke rekening berikut:</p>
        <div class="bank-info">
            <p><strong>Bank BRI</strong></p>
            <p>No. Rekening: <strong>0123-4567-8901-23</strong></p>
            <p>Atas Nama: <strong>Kolam Po'opo</strong></p>
            <p style="margin-top: 8px;">Transfer sebesar <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong> dan upload bukti transfer melalui halaman pesanan Anda.</p>
        </div>
    @elseif($order->payment_method === \App\Enums\PaymentMethod::Midtrans)
        <p>Silakan selesaikan pembayaran melalui halaman pembayaran yang telah disediakan.</p>
    @endif

    @if($order->expires_at)
        <div class="alert-warning">
            ⚠️ <strong>Perhatian:</strong> Selesaikan pembayaran sebelum <strong>{{ $order->expires_at->translatedFormat('d F Y, H:i') }} WIT</strong>. Pesanan akan otomatis dibatalkan jika melewati batas waktu.
        </div>
    @endif

    <div class="btn-block">
        <a href="{{ route('pesanan.detail', $order) }}" class="btn">Lihat Detail Pesanan</a>
    </div>

    <p class="text-muted">Salam hangat,<br>Tim Kolam Po'opo</p>
</x-mail.layout>

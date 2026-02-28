<x-mail.layout :subject="'Pesanan Baru - ' . $order->code" headerSubtitle="Notifikasi Pesanan Baru">
    <h2>Pesanan Baru Masuk 📋</h2>
    <p>Ada pesanan baru yang perlu diperhatikan:</p>

    <div class="info-card">
        <div class="info-row">
            <span class="info-label">Kode Pesanan</span>
            <span class="info-value">{{ $order->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pelanggan</span>
            <span class="info-value">{{ $order->customer_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value">{{ $order->customer_email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Kunjungan</span>
            <span class="info-value">{{ $order->visit_date->translatedFormat('l, d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Metode Pembayaran</span>
            <span class="info-value">{{ $order->payment_method->label() }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total</span>
            <span class="info-value">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <h2>Item Pesanan</h2>
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

    @if($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
        <div class="alert-warning">
            ⚠️ <strong>Perhatian:</strong> Pesanan ini menggunakan metode Transfer Bank. Silakan verifikasi pembayaran setelah pelanggan mengunggah bukti transfer.
        </div>
    @endif

    <div class="btn-block">
        <a href="{{ route('admin.pesanan.detail', $order) }}" class="btn">Lihat Detail Pesanan</a>
    </div>
</x-mail.layout>

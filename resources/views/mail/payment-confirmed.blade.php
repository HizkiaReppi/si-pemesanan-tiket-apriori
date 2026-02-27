<x-mail::message>
# Pembayaran Dikonfirmasi

Halo **{{ $order->customer_name }}**,

Pembayaran untuk pesanan Anda telah **berhasil dikonfirmasi**. Berikut ringkasan pesanan Anda:

**Kode Pesanan:** {{ $order->code }}
**Tanggal Kunjungan:** {{ $order->visit_date->translatedFormat('l, d F Y') }}
**Total Dibayar:** Rp {{ number_format($order->total, 0, ',', '.') }}

---

## Detail Item

<x-mail::table>
| Item | Jumlah | Harga |
|:-----|:------:|------:|
@foreach($order->items as $item)
| {{ $item->item_name }} | {{ $item->quantity }} | Rp {{ number_format($item->subtotal, 0, ',', '.') }} |
@endforeach
</x-mail::table>

---

E-Tiket Anda akan segera dikirim dalam email terpisah. Silakan tunjukkan QR Code di pintu masuk pada tanggal kunjungan.

<x-mail::button :url="route('pesanan.detail', $order)">
Lihat Detail Pesanan
</x-mail::button>

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>

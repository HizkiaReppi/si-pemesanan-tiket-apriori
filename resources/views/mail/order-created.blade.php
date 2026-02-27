<x-mail::message>
# Pesanan Berhasil Dibuat

Halo **{{ $order->customer_name }}**,

Terima kasih telah memesan tiket di **Kolam Po'opo**! Berikut adalah detail pesanan Anda:

**Kode Pesanan:** {{ $order->code }}
**Tanggal Kunjungan:** {{ $order->visit_date->translatedFormat('l, d F Y') }}
**Tipe Hari:** {{ $order->day_type->label() }}

---

## Detail Item

<x-mail::table>
| Item | Jumlah | Harga |
|:-----|:------:|------:|
@foreach($order->items as $item)
| {{ $item->item_name }} | {{ $item->quantity }} | Rp {{ number_format($item->subtotal, 0, ',', '.') }} |
@endforeach
</x-mail::table>

@if($order->discount > 0)
**Diskon:** -Rp {{ number_format($order->discount, 0, ',', '.') }}
@endif

**Total Pembayaran: Rp {{ number_format($order->total, 0, ',', '.') }}**

---

## Metode Pembayaran: {{ $order->payment_method->label() }}

@if($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
Silakan transfer ke rekening berikut:

**Bank BRI**
No. Rekening: **0123-4567-8901-23**
Atas Nama: **Kolam Po'opo**

Transfer sebesar **Rp {{ number_format($order->total, 0, ',', '.') }}** dan upload bukti transfer melalui halaman pesanan Anda.
@elseif($order->payment_method === \App\Enums\PaymentMethod::Midtrans)
Silakan selesaikan pembayaran melalui halaman pembayaran yang telah disediakan.
@endif

@if($order->expires_at)
> **Perhatian:** Selesaikan pembayaran sebelum **{{ $order->expires_at->translatedFormat('d F Y, H:i') }} WIT**. Pesanan akan otomatis dibatalkan jika melewati batas waktu.
@endif

<x-mail::button :url="route('pesanan.detail', $order)">
Lihat Detail Pesanan
</x-mail::button>

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>

<x-mail::message>
# Kunjungan Selesai

Halo **{{ $order->customer_name }}**,

Terima kasih telah mengunjungi **Kolam Po'opo**! Kami harap Anda menikmati pengalaman berendam air panas alami bersama kami.

**Kode Pesanan:** {{ $order->code }}
**Tanggal Kunjungan:** {{ $order->visit_date->translatedFormat('l, d F Y') }}

---

## Ringkasan Kunjungan

<x-mail::table>
| Item | Jumlah | Harga |
|:-----|:------:|------:|
@foreach($order->items as $item)
| {{ $item->item_name }} | {{ $item->quantity }} | Rp {{ number_format($item->subtotal, 0, ',', '.') }} |
@endforeach
</x-mail::table>

**Total:** Rp {{ number_format($order->total, 0, ',', '.') }}

---

Kami sangat senang Anda memilih Kolam Po'opo. Jangan ragu untuk berkunjung kembali!

<x-mail::button :url="route('tiket.index')">
Pesan Tiket Lagi
</x-mail::button>

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>

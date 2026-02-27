<x-mail::message>
# E-Tiket Anda

Halo **{{ $order->customer_name }}**,

Berikut adalah e-tiket untuk kunjungan Anda ke **Kolam Po'opo**.

**Kode Pesanan:** {{ $order->code }}
**Tanggal Kunjungan:** {{ $order->visit_date->translatedFormat('l, d F Y') }}
**Tipe Hari:** {{ $order->day_type->label() }}

---

## Detail Tiket

<x-mail::table>
| Item | Jumlah |
|:-----|:------:|
@foreach($order->items as $item)
| {{ $item->item_name }} | {{ $item->quantity }} |
@endforeach
</x-mail::table>

---

## Petunjuk Penggunaan

1. Tunjukkan QR Code atau kode pesanan **{{ $order->code }}** di pintu masuk
2. Pastikan datang sesuai tanggal kunjungan
3. QR Code hanya berlaku untuk satu kali masuk
4. Jam operasional: **08:00 - 18:00 WITA**

<x-mail::button :url="route('pesanan.detail', $order)">
Lihat E-Tiket
</x-mail::button>

> Simpan email ini sebagai bukti tiket Anda. Jika mengalami kendala, hubungi kami melalui halaman kontak.

Selamat menikmati kunjungan Anda!<br>
{{ config('app.name') }}
</x-mail::message>

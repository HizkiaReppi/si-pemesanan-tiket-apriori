<x-mail::message>
# Pesanan Baru Masuk

Ada pesanan baru yang perlu diperhatikan:

**Kode Pesanan:** {{ $order->code }}
**Pelanggan:** {{ $order->customer_name }} ({{ $order->customer_email }})
**Tanggal Kunjungan:** {{ $order->visit_date->translatedFormat('l, d F Y') }}
**Metode Pembayaran:** {{ $order->payment_method->label() }}
**Total:** Rp {{ number_format($order->total, 0, ',', '.') }}

---

## Item Pesanan

<x-mail::table>
| Item | Jumlah | Harga |
|:-----|:------:|------:|
@foreach($order->items as $item)
| {{ $item->item_name }} | {{ $item->quantity }} | Rp {{ number_format($item->subtotal, 0, ',', '.') }} |
@endforeach
</x-mail::table>

@if($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
> **Perhatian:** Pesanan ini menggunakan metode Transfer Bank. Silakan verifikasi pembayaran setelah pelanggan mengunggah bukti transfer.
@endif

<x-mail::button :url="route('admin.pesanan.detail', $order)">
Lihat Detail Pesanan
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>

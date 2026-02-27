<?php

use App\Models\Order;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('E-Tiket')] #[Layout('layouts.app')] class extends Component {
    public Order $order;

    public string $qrSvg = '';

    public function mount(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        if (! $order->isPaid()) {
            abort(404, 'Tiket belum tersedia.');
        }

        $this->order = $order->load('items');

        if ($order->qr_token) {
            $this->qrSvg = app(QrCodeService::class)->generateSvg($order->qr_token);
        }
    }
}; ?>

<div>
    <div class="mx-auto max-w-lg">
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Header -->
            <div class="border-b border-zinc-100 p-6 text-center dark:border-zinc-800">
                <h1 class="text-xl font-bold text-zinc-900 dark:text-white">E-Tiket Kolam Po'opo</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $order->code }}</p>
            </div>

            <!-- QR Code -->
            @if($qrSvg)
                <div class="flex justify-center border-b border-zinc-100 p-6 dark:border-zinc-800">
                    <div class="rounded-lg bg-white p-4">
                        {!! $qrSvg !!}
                    </div>
                </div>
            @endif

            <!-- Ticket Info -->
            <div class="space-y-3 p-6">
                <div class="flex justify-between">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Nama</span>
                    <span class="font-medium text-zinc-900 dark:text-white">{{ $order->customer_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Tanggal Kunjungan</span>
                    <span class="font-medium text-zinc-900 dark:text-white">
                        {{ $order->visit_date->translatedFormat('d F Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Tipe Hari</span>
                    <span class="font-medium text-zinc-900 dark:text-white">{{ $order->day_type->label() }}</span>
                </div>

                <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800">
                    <p class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">Item:</p>
                    @foreach($order->items as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $item->item_name }} × {{ $item->quantity }}</span>
                            <span class="text-zinc-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                    <span class="font-semibold text-zinc-900 dark:text-white">Total</span>
                    <span class="font-bold text-zinc-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Notes -->
            <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                <p class="text-center text-xs text-zinc-500 dark:text-zinc-400">
                    Tunjukkan QR code ini di gerbang masuk. Tiket hanya berlaku pada tanggal kunjungan.
                </p>
            </div>
        </div>
    </div>
</div>

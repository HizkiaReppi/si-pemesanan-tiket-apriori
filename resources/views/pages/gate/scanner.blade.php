<?php

use App\Actions\RedeemTicket;
use App\Actions\ValidateTicket;
use App\Enums\CheckInStatus;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pemindai Tiket')] #[Layout('layouts.app')] class extends Component {
    public string $qrInput = '';

    public ?string $resultStatus = null;

    public ?string $resultMessage = null;

    public ?array $orderInfo = null;

    public function scan(): void
    {
        $this->validate(['qrInput' => 'required|string']);

        // Parse QR code: format is "ORDER_CODE|TOKEN"
        $parts = explode('|', $this->qrInput);
        if (count($parts) !== 2) {
            $this->resultStatus = 'error';
            $this->resultMessage = 'Format QR code tidak valid.';
            $this->orderInfo = null;

            return;
        }

        [$orderCode, $token] = $parts;

        $order = Order::where('code', $orderCode)->first();
        if (! $order) {
            $this->resultStatus = 'error';
            $this->resultMessage = 'Pesanan tidak ditemukan.';
            $this->orderInfo = null;

            return;
        }

        // Validate
        $validator = app(ValidateTicket::class);
        $status = $validator->execute($order, $token);

        if ($status === CheckInStatus::Valid) {
            // Redeem
            $redeemer = app(RedeemTicket::class);
            $checkIn = $redeemer->execute($order, $token, auth()->id());

            $this->resultStatus = 'success';
            $this->resultMessage = 'Tiket valid! Check-in berhasil.';
        } else {
            $this->resultStatus = 'error';
            $this->resultMessage = $status->label();
        }

        $this->orderInfo = [
            'code' => $order->code,
            'customer' => $order->customer_name,
            'visit_date' => $order->visit_date->format('d/m/Y'),
            'items' => $order->items->map(fn ($i) => "{$i->item_name} × {$i->quantity}")->implode(', '),
            'total' => number_format($order->total, 0, ',', '.'),
            'status' => $order->fresh()->status->label(),
        ];

        $this->qrInput = '';
    }

    public function clearResult(): void
    {
        $this->resultStatus = null;
        $this->resultMessage = null;
        $this->orderInfo = null;
    }
}; ?>

<div>
    <div class="mx-auto max-w-2xl space-y-6">
        <flux:heading size="xl" class="text-center">Pemindai Tiket</flux:heading>
        <p class="text-center text-zinc-600 dark:text-zinc-400">Pindai QR code dari e-tiket pengunjung atau masukkan kode secara manual.</p>

        <!-- Manual Input -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form wire:submit="scan" class="space-y-4">
                <flux:input wire:model="qrInput" label="Kode QR / Kode Pesanan" placeholder="Scan atau masukkan kode..." autofocus />
                <flux:button type="submit" variant="primary" class="w-full" icon="qr-code">
                    Validasi Tiket
                </flux:button>
            </form>
        </div>

        <!-- Result -->
        @if($resultStatus)
            <div class="rounded-xl border-2 p-6 {{ $resultStatus === 'success' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-red-500 bg-red-50 dark:bg-red-900/20' }}">
                <div class="mb-4 flex items-center justify-center gap-3">
                    @if($resultStatus === 'success')
                        <div class="flex size-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                            <flux:icon name="check-circle" class="size-8 text-green-600" />
                        </div>
                    @else
                        <div class="flex size-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                            <flux:icon name="x-circle" class="size-8 text-red-600" />
                        </div>
                    @endif
                    <p class="text-lg font-bold {{ $resultStatus === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                        {{ $resultMessage }}
                    </p>
                </div>

                @if($orderInfo)
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Kode Pesanan</span>
                            <span class="font-mono font-medium text-zinc-900 dark:text-white">{{ $orderInfo['code'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Pelanggan</span>
                            <span class="text-zinc-900 dark:text-white">{{ $orderInfo['customer'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Tanggal Kunjungan</span>
                            <span class="text-zinc-900 dark:text-white">{{ $orderInfo['visit_date'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Item</span>
                            <span class="text-zinc-900 dark:text-white">{{ $orderInfo['items'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Total</span>
                            <span class="font-medium text-zinc-900 dark:text-white">Rp {{ $orderInfo['total'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Status</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $orderInfo['status'] }}</span>
                        </div>
                    </div>
                @endif

                <flux:button wire:click="clearResult" variant="ghost" class="mt-4 w-full">Pindai Lagi</flux:button>
            </div>
        @endif
    </div>
</div>

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

<div
    x-data="{
        mode: 'camera',
        scanner: null,
        cameraActive: false,
        cameraError: null,

        async initCamera() {
            if (this.scanner) {
                await this.stopCamera();
            }

            this.cameraError = null;

            try {
                const { Html5Qrcode } = await import('https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js');
            } catch (e) {
                // Library loaded via script tag
            }

            if (typeof Html5Qrcode === 'undefined') {
                this.cameraError = 'Pustaka pemindai tidak tersedia. Gunakan mode manual.';
                return;
            }

            this.scanner = new Html5Qrcode('qr-reader');

            try {
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    (decodedText) => {
                        $wire.set('qrInput', decodedText);
                        $wire.call('scan');
                        this.stopCamera();
                    },
                    () => {}
                );
                this.cameraActive = true;
            } catch (err) {
                this.cameraError = 'Tidak dapat mengakses kamera. Pastikan izin kamera diberikan atau gunakan mode manual.';
                this.cameraActive = false;
            }
        },

        async stopCamera() {
            if (this.scanner && this.cameraActive) {
                try {
                    await this.scanner.stop();
                } catch (e) {}
                this.cameraActive = false;
            }
        },

        switchMode(newMode) {
            this.mode = newMode;
            if (newMode === 'camera') {
                this.$nextTick(() => this.initCamera());
            } else {
                this.stopCamera();
            }
        }
    }"
    x-init="$nextTick(() => { if (mode === 'camera') initCamera(); })"
    @beforeunmount.window="stopCamera()"
>
    <div class="mx-auto max-w-2xl space-y-6">
        <flux:heading size="xl" class="text-center">Pemindai Tiket</flux:heading>
        <p class="text-center text-zinc-600 dark:text-zinc-400">Scan QR code e-tiket atau masukkan kode secara manual.</p>

        {{-- Mode Toggle --}}
        <div class="flex items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white p-2 dark:border-zinc-700 dark:bg-zinc-900">
            <button
                @click="switchMode('camera')"
                :class="mode === 'camera' ? 'bg-sky-600 text-white shadow-sm' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                class="flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                Scan Kamera
            </button>
            <button
                @click="switchMode('manual')"
                :class="mode === 'manual' ? 'bg-sky-600 text-white shadow-sm' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                class="flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Input Manual
            </button>
        </div>

        {{-- Camera Scanner --}}
        <div x-show="mode === 'camera'" x-cloak class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <template x-if="cameraError">
                <div class="rounded-lg bg-amber-50 p-4 text-center dark:bg-amber-900/20">
                    <p class="text-sm text-amber-700 dark:text-amber-400" x-text="cameraError"></p>
                    <button @click="initCamera()" class="mt-3 text-sm font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400">Coba lagi</button>
                </div>
            </template>
            <div id="qr-reader" class="mx-auto overflow-hidden rounded-lg" style="max-width: 400px;"></div>
            <p class="mt-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                Arahkan kamera ke QR code pada e-tiket pengunjung
            </p>
        </div>

        {{-- Manual Input --}}
        <div x-show="mode === 'manual'" x-cloak class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form wire:submit="scan" class="space-y-4">
                <flux:input wire:model="qrInput" label="Kode QR / Kode Pesanan" placeholder="Masukkan kode dari e-tiket..." autofocus />
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

                <flux:button wire:click="clearResult" variant="ghost" class="mt-4 w-full" x-on:click="if (mode === 'camera') { $nextTick(() => initCamera()); }">
                    Pindai Lagi
                </flux:button>
            </div>
        @endif
    </div>
</div>

@script
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endscript

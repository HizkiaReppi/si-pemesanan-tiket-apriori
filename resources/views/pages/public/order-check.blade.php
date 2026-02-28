<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Cek Status Pesanan')] #[Layout('layouts.guest')] class extends Component {
    public string $searchQuery = '';

    public string $searchType = 'code';

    public ?Order $foundOrder = null;

    public bool $searched = false;

    public string $qrSvg = '';

    /** @var \Illuminate\Support\Collection<int, Order>|null */
    public $foundOrders = null;

    public function search(): void
    {
        $key = 'order-check:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            session()->flash('error', 'Terlalu banyak percobaan pencarian. Coba lagi dalam beberapa menit.');

            return;
        }

        RateLimiter::hit($key, 60);

        $this->validate([
            'searchQuery' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'searchQuery.required' => 'Masukkan kode pesanan, email, atau nomor telepon.',
            'searchQuery.min' => 'Minimal 3 karakter.',
        ]);

        $this->searched = true;
        $this->foundOrder = null;
        $this->foundOrders = null;
        $this->qrSvg = '';

        $query = Order::with(['items', 'payment', 'checkIns']);

        if ($this->searchType === 'code') {
            $order = $query->where('code', trim($this->searchQuery))->first();

            if ($order) {
                $this->foundOrder = $order;

                if ($order->isPaid() && $order->qr_token) {
                    $this->qrSvg = app(QrCodeService::class)->generateSvg($order->qr_token, 200);
                }
            }
        } elseif ($this->searchType === 'email') {
            $orders = $query->where('customer_email', trim($this->searchQuery))
                ->latest()
                ->take(10)
                ->get();

            $this->foundOrders = $orders->isEmpty() ? null : $orders;
        } elseif ($this->searchType === 'phone') {
            $orders = $query->where('customer_phone', trim($this->searchQuery))
                ->latest()
                ->take(10)
                ->get();

            $this->foundOrders = $orders->isEmpty() ? null : $orders;
        }
    }

    public function selectOrder(int $orderId): void
    {
        $this->foundOrder = Order::with(['items', 'payment', 'checkIns'])->find($orderId);
        $this->foundOrders = null;
        $this->qrSvg = '';

        if ($this->foundOrder?->isPaid() && $this->foundOrder->qr_token) {
            $this->qrSvg = app(QrCodeService::class)->generateSvg($this->foundOrder->qr_token, 200);
        }
    }

    public function clearSearch(): void
    {
        $this->searchQuery = '';
        $this->foundOrder = null;
        $this->foundOrders = null;
        $this->searched = false;
        $this->qrSvg = '';
    }
}; ?>

<div class="relative">
    <!-- Decorative gradient header -->
    <div class="absolute inset-x-0 top-0 h-64 overflow-hidden bg-linear-to-br from-sky-600 via-sky-700 to-sky-900 dark:from-sky-800 dark:via-sky-900 dark:to-zinc-900">
        <div class="animate-float-gentle absolute -top-10 left-1/3 size-36 rounded-full bg-white/10 blur-2xl"></div>
        <div class="animate-float-slow absolute bottom-0 right-1/4 size-44 rounded-full bg-sky-400/10 blur-2xl"></div>
        <div class="animate-float-reverse absolute left-1/4 top-1/4 size-28 rounded-full bg-cyan-200/5 blur-2xl"></div>
        <div class="absolute -bottom-1 left-0 w-full">
            <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0,40 C360,100 1080,0 1440,60 L1440,100 L0,100Z" class="fill-zinc-50 dark:fill-zinc-900" />
            </svg>
        </div>
    </div>

    <div class="relative mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-white/20 shadow-lg backdrop-blur-sm">
            <flux:icon name="magnifying-glass" class="size-8 text-white" />
        </div>
        <h1 class="text-3xl font-bold text-white">Cek Status Pesanan</h1>
        <p class="mt-2 text-sky-100">
            Cek status pesanan atau tiket Anda tanpa harus masuk ke akun.
        </p>
    </div>

    <!-- Search Form -->
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="search">
            <div class="mb-4 flex gap-2">
                <button type="button" wire:click="$set('searchType', 'code')"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $searchType === 'code' ? 'bg-sky-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                    Kode Pesanan
                </button>
                <button type="button" wire:click="$set('searchType', 'email')"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $searchType === 'email' ? 'bg-sky-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                    Email
                </button>
                <button type="button" wire:click="$set('searchType', 'phone')"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $searchType === 'phone' ? 'bg-sky-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                    No. Telepon
                </button>
            </div>

            <div class="flex gap-3">
                <div class="flex-1">
                    <flux:input
                        wire:model="searchQuery"
                        placeholder="{{ match($searchType) { 'code' => 'Masukkan kode pesanan (contoh: ORD-20250101-ABCDEF)', 'email' => 'Masukkan alamat email', 'phone' => 'Masukkan nomor telepon' } }}"
                        icon="{{ match($searchType) { 'code' => 'hashtag', 'email' => 'envelope', 'phone' => 'phone' } }}"
                    />
                </div>
                <flux:button type="submit" variant="primary" icon="magnifying-glass">
                    Cari
                </flux:button>
            </div>
            @error('searchQuery')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </form>
    </div>

    <!-- Multiple Results (email/phone search) -->
    @if($foundOrders && $foundOrders->isNotEmpty())
        <div class="mt-6 space-y-3">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                Ditemukan {{ $foundOrders->count() }} pesanan
            </h2>
            @foreach($foundOrders as $order)
                @php
                    $statusColors = [
                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                        'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                        'gray' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                    ];
                    $badgeClass = $statusColors[$order->status->color()] ?? $statusColors['gray'];
                @endphp
                <button wire:click="selectOrder({{ $order->id }})"
                    class="w-full rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-sky-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-sky-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-mono font-semibold text-zinc-900 dark:text-white">{{ $order->code }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $order->visit_date->translatedFormat('d F Y') }} · {{ $order->customer_name }}
                            </p>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                {{ $order->status->label() }}
                            </span>
                            <flux:icon name="chevron-right" variant="mini" class="size-5 text-zinc-400" />
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
    @endif

    <!-- Single Result Detail -->
    @if($foundOrder)
        <div class="mt-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Detail Pesanan</h2>
                <flux:button wire:click="clearSearch" variant="ghost" size="sm" icon="arrow-left">
                    Cari Lagi
                </flux:button>
            </div>

            @php
                $statusColors = [
                    'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                    'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                    'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                    'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                    'gray' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                ];
                $badgeClass = $statusColors[$foundOrder->status->color()] ?? $statusColors['gray'];
            @endphp

            <!-- Status & Code -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-mono text-lg font-semibold text-zinc-900 dark:text-white">{{ $foundOrder->code }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Dipesan {{ $foundOrder->created_at->translatedFormat('d F Y, H:i') }}
                        </p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full px-4 py-2 text-sm font-semibold {{ $badgeClass }}">
                        {{ $foundOrder->status->label() }}
                    </span>
                </div>
            </div>

            <!-- Visit & Customer Info -->
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-3 font-semibold text-zinc-900 dark:text-white">Info Kunjungan</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 text-zinc-400" />
                            <span class="text-zinc-700 dark:text-zinc-300">{{ $foundOrder->visit_date->translatedFormat('l, d F Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:icon name="tag" variant="mini" class="size-4 text-zinc-400" />
                            <span class="text-zinc-700 dark:text-zinc-300">{{ $foundOrder->day_type->label() }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-3 font-semibold text-zinc-900 dark:text-white">Info Pemesan</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <flux:icon name="user" variant="mini" class="size-4 text-zinc-400" />
                            <span class="text-zinc-700 dark:text-zinc-300">{{ $foundOrder->customer_name }}</span>
                        </div>
                        @if($foundOrder->customer_email)
                            <div class="flex items-center gap-2">
                                <flux:icon name="envelope" variant="mini" class="size-4 text-zinc-400" />
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $foundOrder->customer_email }}</span>
                            </div>
                        @endif
                        @if($foundOrder->customer_phone)
                            <div class="flex items-center gap-2">
                                <flux:icon name="phone" variant="mini" class="size-4 text-zinc-400" />
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $foundOrder->customer_phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-6 py-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Item Pesanan</h3>
                </div>
                @foreach($foundOrder->items as $item)
                    <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-3 last:border-b-0 dark:border-zinc-800">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $item->item_name }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $item->quantity }} × Rp {{ number_format($item->item_price, 0, ',', '.') }}</p>
                        </div>
                        <p class="font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
                <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <div class="flex justify-between text-lg font-bold text-zinc-900 dark:text-white">
                        <span>Total</span>
                        <span>Rp {{ number_format($foundOrder->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            @if($foundOrder->payment)
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-3 font-semibold text-zinc-900 dark:text-white">Pembayaran</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Metode</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $foundOrder->payment_method->label() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Status</p>
                            @php
                                $paymentBadge = $statusColors[$foundOrder->payment->status->color()] ?? $statusColors['gray'];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $paymentBadge }}">
                                {{ $foundOrder->payment->status->label() }}
                            </span>
                        </div>
                        @if($foundOrder->payment->paid_at)
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Dibayar</p>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $foundOrder->payment->paid_at->translatedFormat('d F Y, H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- QR Code -->
            @if($foundOrder->isPaid() && $qrSvg)
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-3 font-semibold text-zinc-900 dark:text-white">E-Tiket QR Code</h3>
                    <div class="mx-auto flex w-fit items-center justify-center rounded-xl bg-white p-3">
                        {!! $qrSvg !!}
                    </div>
                    <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                        Tunjukkan QR code ini di pintu masuk pada tanggal kunjungan.
                    </p>
                </div>
            @endif

            <!-- Check-in Status -->
            @if($foundOrder->isPaid() && $foundOrder->checkIns->isNotEmpty())
                @php $latestCheckIn = $foundOrder->checkIns->sortByDesc('scanned_at')->first(); @endphp
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <div class="flex items-center gap-3">
                        <flux:icon name="check-circle" class="size-6 text-green-500" />
                        <div>
                            <p class="font-medium text-green-800 dark:text-green-400">Sudah Check-in</p>
                            <p class="text-sm text-green-600 dark:text-green-400/80">
                                {{ $latestCheckIn->scanned_at->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Link to Full Detail -->
            <div class="flex justify-center">
                <flux:button :href="route('pesanan.detail', $foundOrder)" wire:navigate variant="primary" icon="arrow-top-right-on-square">
                    Lihat Detail Lengkap
                </flux:button>
            </div>
        </div>
    @elseif($searched && ! $foundOrders)
        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon name="magnifying-glass" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
            <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Pesanan Tidak Ditemukan</h3>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                Pastikan data yang Anda masukkan sudah benar dan coba lagi.
            </p>
        </div>
    @endif
</div>
</div>

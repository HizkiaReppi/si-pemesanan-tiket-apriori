<?php

use App\Actions\ApplyVoucher;
use App\Actions\CreateOrder;
use App\Enums\PaymentMethod;
use App\Models\Addon;
use App\Models\TicketType;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\PricingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Pesan Tiket - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
    // Step management (1: Date, 2: Tickets, 3: Add-ons, 4: Checkout)
    public int $step = 1;

    // Step 1: Date
    public string $visitDate = '';

    public string $dayTypeLabel = '';

    public bool $isWeekend = false;

    // Step 2: Tickets
    public array $ticketTypes = [];

    /** @var array<string, int> */
    public array $ticketQuantities = [];

    // Step 3: Add-ons
    public array $addons = [];

    /** @var array<string, int> */
    public array $addonQuantities = [];

    // Step 4: Checkout
    #[Validate('required|string|max:255')]
    public string $customerName = '';

    #[Validate('required|email|max:255')]
    public string $customerEmail = '';

    #[Validate('required|string|max:20')]
    public string $customerPhone = '';

    #[Validate('required|in:midtrans,bank_transfer')]
    public string $paymentMethod = 'bank_transfer';

    public string $voucherCode = '';

    public ?array $appliedVoucher = null;

    public float $discount = 0;

    public function mount(): void
    {
        $this->visitDate = now()->addDay()->format('Y-m-d');
        $this->updateDayType();
        $this->loadProducts();

        if (auth()->check()) {
            $this->customerName = auth()->user()->name;
            $this->customerEmail = auth()->user()->email;
            $this->customerPhone = auth()->user()->phone ?? '';
        }
    }

    public function updatedVisitDate(): void
    {
        $this->updateDayType();
        $this->loadProducts();
        $this->ticketQuantities = [];
        $this->addonQuantities = [];
    }

    public function updateDayType(): void
    {
        $pricingService = app(PricingService::class);
        $dayType = $pricingService->getDayType($this->visitDate);
        $this->dayTypeLabel = $dayType->label();
        $this->isWeekend = \Carbon\Carbon::parse($this->visitDate)->isWeekend();
    }

    public function loadProducts(): void
    {
        $pricingService = app(PricingService::class);

        $this->ticketTypes = TicketType::active()
            ->ordered()
            ->with('prices')
            ->get()
            ->map(function ($ticket) use ($pricingService) {
                $weekdayPrice = $ticket->prices->firstWhere('day_type', \App\Enums\DayType::Weekday);
                $weekendPrice = $ticket->prices->firstWhere('day_type', \App\Enums\DayType::Weekend);

                return [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'description' => $ticket->description,
                    'price' => $pricingService->getTicketPrice($ticket, $this->visitDate),
                    'weekday_price' => $weekdayPrice ? (float) $weekdayPrice->price : 0,
                    'weekend_price' => $weekendPrice ? (float) $weekendPrice->price : 0,
                ];
            })
            ->toArray();

        $this->addons = Addon::active()
            ->ordered()
            ->get()
            ->map(fn ($addon) => [
                'id' => $addon->id,
                'name' => $addon->name,
                'description' => $addon->description,
                'price' => (float) $addon->price,
            ])
            ->toArray();
    }

    // Ticket quantity methods
    public function incrementTicket(int $id): void
    {
        $key = "ticket_{$id}";
        $this->ticketQuantities[$key] = ($this->ticketQuantities[$key] ?? 0) + 1;
    }

    public function decrementTicket(int $id): void
    {
        $key = "ticket_{$id}";
        $current = $this->ticketQuantities[$key] ?? 0;

        if ($current > 0) {
            $this->ticketQuantities[$key] = $current - 1;
        }

        if (($this->ticketQuantities[$key] ?? 0) === 0) {
            unset($this->ticketQuantities[$key]);
        }
    }

    public function setTicketQuantity(int $id, int $quantity): void
    {
        $key = "ticket_{$id}";

        if ($quantity > 0) {
            $this->ticketQuantities[$key] = min($quantity, 50);
        } else {
            unset($this->ticketQuantities[$key]);
        }
    }

    // Addon quantity methods
    public function incrementAddon(int $id): void
    {
        $key = "addon_{$id}";
        $this->addonQuantities[$key] = ($this->addonQuantities[$key] ?? 0) + 1;
    }

    public function decrementAddon(int $id): void
    {
        $key = "addon_{$id}";
        $current = $this->addonQuantities[$key] ?? 0;

        if ($current > 0) {
            $this->addonQuantities[$key] = $current - 1;
        }

        if (($this->addonQuantities[$key] ?? 0) === 0) {
            unset($this->addonQuantities[$key]);
        }
    }

    public function setAddonQuantity(int $id, int $quantity): void
    {
        $key = "addon_{$id}";

        if ($quantity > 0) {
            $this->addonQuantities[$key] = min($quantity, 50);
        } else {
            unset($this->addonQuantities[$key]);
        }
    }

    // Step navigation
    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;

            return;
        }

        if ($step === 2 && empty($this->visitDate)) {
            return;
        }

        if ($step === 3 && $this->getTicketCount() === 0) {
            $this->addError('tickets', 'Pilih minimal 1 tiket untuk melanjutkan.');

            return;
        }

        $this->step = $step;
    }

    public function nextStep(): void
    {
        $this->goToStep($this->step + 1);
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    // Computed helpers
    public function getTicketCount(): int
    {
        return array_sum($this->ticketQuantities);
    }

    public function getSubtotalProperty(): float
    {
        $subtotal = 0;

        foreach ($this->ticketTypes as $ticket) {
            $key = "ticket_{$ticket['id']}";
            $qty = $this->ticketQuantities[$key] ?? 0;
            $subtotal += $ticket['price'] * $qty;
        }

        foreach ($this->addons as $addon) {
            $key = "addon_{$addon['id']}";
            $qty = $this->addonQuantities[$key] ?? 0;
            $subtotal += $addon['price'] * $qty;
        }

        return $subtotal;
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - $this->discount);
    }

    public function getSelectedItemsProperty(): array
    {
        $items = [];

        foreach ($this->ticketTypes as $ticket) {
            $key = "ticket_{$ticket['id']}";
            $qty = $this->ticketQuantities[$key] ?? 0;

            if ($qty > 0) {
                $items[] = [
                    'type' => 'ticket',
                    'id' => $ticket['id'],
                    'name' => $ticket['name'],
                    'price' => $ticket['price'],
                    'quantity' => $qty,
                ];
            }
        }

        foreach ($this->addons as $addon) {
            $key = "addon_{$addon['id']}";
            $qty = $this->addonQuantities[$key] ?? 0;

            if ($qty > 0) {
                $items[] = [
                    'type' => 'addon',
                    'id' => $addon['id'],
                    'name' => $addon['name'],
                    'price' => $addon['price'],
                    'quantity' => $qty,
                ];
            }
        }

        return $items;
    }

    // Voucher
    public function applyVoucher(): void
    {
        if (empty($this->voucherCode)) {
            return;
        }

        try {
            $result = app(ApplyVoucher::class)->calculate($this->voucherCode, $this->subtotal);
            $this->appliedVoucher = [
                'id' => $result['voucher']->id,
                'code' => $result['voucher']->code,
                'name' => $result['voucher']->name,
            ];
            $this->discount = $result['discount'];
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('voucherCode', $e->getMessage());
            $this->appliedVoucher = null;
            $this->discount = 0;
        }
    }

    public function removeVoucher(): void
    {
        $this->appliedVoucher = null;
        $this->discount = 0;
        $this->voucherCode = '';
    }

    // Place order
    public function placeOrder(): void
    {
        $this->validate();

        $items = $this->selectedItems;

        if (empty($items)) {
            $this->addError('tickets', 'Pilih minimal 1 tiket.');
            $this->step = 2;

            return;
        }

        $cart = [
            'visit_date' => $this->visitDate,
            'items' => $items,
        ];

        $order = app(CreateOrder::class)->execute(
            cart: $cart,
            customer: [
                'name' => $this->customerName,
                'email' => $this->customerEmail,
                'phone' => $this->customerPhone,
            ],
            paymentMethod: PaymentMethod::from($this->paymentMethod),
            userId: auth()->id(),
            discount: $this->discount,
        );

        // Record voucher usage
        if ($this->appliedVoucher) {
            $voucher = \App\Models\Voucher::find($this->appliedVoucher['id']);

            if ($voucher) {
                app(ApplyVoucher::class)->record($voucher, $order, $this->discount);
            }
        }

        // Midtrans snap token
        if ($order->payment_method === PaymentMethod::Midtrans) {
            try {
                $midtrans = app(MidtransService::class);
                $snap = $midtrans->createSnapToken($order);

                $order->payment?->update([
                    'midtrans_snap_token' => $snap['token'],
                ]);
            } catch (\Exception $e) {
                // Snap token generation failed, order still created
                \Illuminate\Support\Facades\Log::error('Midtrans snap token failed', [
                    'order' => $order->code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Clear old session cart if any
        app(CartService::class)->clear();

        $this->redirect(route('pesanan.detail', $order), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">Pesan Tiket</h1>
        <p class="mx-auto mt-3 max-w-2xl text-zinc-600 dark:text-zinc-400">
            Ikuti langkah-langkah berikut untuk memesan tiket Kolam Po'opo
        </p>
    </div>

    <!-- Step Indicator -->
    <div class="mb-10">
        <div class="flex items-center justify-center gap-2">
            @php
                $steps = [
                    ['num' => 1, 'label' => 'Tanggal'],
                    ['num' => 2, 'label' => 'Tiket'],
                    ['num' => 3, 'label' => 'Add-on'],
                    ['num' => 4, 'label' => 'Pembayaran'],
                ];
            @endphp
            @foreach($steps as $i => $s)
                <div class="flex items-center">
                    <button
                        wire:click="goToStep({{ $s['num'] }})"
                        class="flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors
                            {{ $step === $s['num']
                                ? 'bg-sky-600 text-white shadow-md'
                                : ($step > $s['num']
                                    ? 'bg-sky-100 text-sky-700 hover:bg-sky-200 dark:bg-sky-900/30 dark:text-sky-400'
                                    : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500') }}"
                        {{ $step < $s['num'] && ($s['num'] > 2 ?? true) ? 'disabled' : '' }}
                    >
                        @if($step > $s['num'])
                            <flux:icon name="check" variant="mini" class="size-4" />
                        @else
                            <span class="flex size-5 items-center justify-center rounded-full text-xs
                                {{ $step === $s['num'] ? 'bg-white/20' : '' }}">
                                {{ $s['num'] }}
                            </span>
                        @endif
                        <span class="hidden sm:inline">{{ $s['label'] }}</span>
                    </button>
                    @if($i < count($steps) - 1)
                        <div class="mx-1 h-px w-6 sm:w-12 {{ $step > $s['num'] ? 'bg-sky-400' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ======================== Step 1: Date ======================== --}}
    @if($step === 1)
        <div class="mx-auto max-w-2xl">
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6 text-center">
                    <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-sky-100 dark:bg-sky-900/30">
                        <flux:icon name="calendar-days" class="size-8 text-sky-600 dark:text-sky-400" />
                    </div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Pilih Tanggal Kunjungan</h2>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Harga tiket menyesuaikan berdasarkan hari kerja atau akhir pekan</p>
                </div>

                <div class="mx-auto max-w-xs">
                    <flux:input
                        wire:model.live="visitDate"
                        type="date"
                        label="Tanggal"
                        :min="now()->addDay()->format('Y-m-d')"
                    />
                </div>

                @if($visitDate)
                    <div class="mt-6 text-center">
                        <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium
                            {{ $isWeekend ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' }}">
                            <flux:icon name="calendar-days" variant="mini" class="size-4" />
                            {{ \Carbon\Carbon::parse($visitDate)->translatedFormat('l, d F Y') }}
                            &mdash; {{ $dayTypeLabel }}
                        </span>
                    </div>
                @endif

                <div class="mt-8 flex justify-end">
                    <flux:button wire:click="nextStep" variant="primary" icon-trailing="arrow-right" :disabled="empty($visitDate)">
                        Pilih Tiket
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================== Step 2: Tickets ======================== --}}
    @if($step === 2)
        <div>
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/30">
                        <flux:icon name="ticket" variant="mini" class="size-5 text-sky-600 dark:text-sky-400" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Pilih Tiket</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ \Carbon\Carbon::parse($visitDate)->translatedFormat('l, d F Y') }} — {{ $dayTypeLabel }}
                        </p>
                    </div>
                </div>
            </div>

            @error('tickets')
                <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                    {{ $message }}
                </div>
            @enderror

            @if(empty($ticketTypes))
                <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="ticket" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-4 text-zinc-500 dark:text-zinc-400">Belum ada jenis tiket tersedia.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($ticketTypes as $ticket)
                        @php $tKey = "ticket_{$ticket['id']}"; $tQty = $ticketQuantities[$tKey] ?? 0; @endphp
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900
                            {{ $tQty > 0 ? 'ring-2 ring-sky-500/50' : '' }}">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ $ticket['name'] }}</h3>
                                    @if($ticket['description'])
                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $ticket['description'] }}</p>
                                    @endif
                                    <div class="mt-3 flex items-baseline gap-1">
                                        <span class="text-2xl font-bold text-zinc-900 dark:text-white">
                                            Rp {{ number_format($ticket['price'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-sm text-zinc-400">/orang</span>
                                    </div>
                                    <!-- Price comparison -->
                                    <div class="mt-2 flex gap-3 text-xs text-zinc-400 dark:text-zinc-500">
                                        <span class="{{ !$isWeekend ? 'font-semibold text-sky-600 dark:text-sky-400' : '' }}">
                                            Hari Kerja: Rp {{ number_format($ticket['weekday_price'], 0, ',', '.') }}
                                        </span>
                                        <span>·</span>
                                        <span class="{{ $isWeekend ? 'font-semibold text-amber-600 dark:text-amber-400' : '' }}">
                                            Akhir Pekan: Rp {{ number_format($ticket['weekend_price'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-2">
                                    <flux:button
                                        wire:click="decrementTicket({{ $ticket['id'] }})"
                                        size="sm"
                                        variant="ghost"
                                        icon="minus"
                                        :disabled="$tQty === 0"
                                        class="size-10 rounded-full"
                                    />
                                    <input
                                        type="number"
                                        value="{{ $tQty }}"
                                        wire:change="setTicketQuantity({{ $ticket['id'] }}, $event.target.value)"
                                        min="0"
                                        max="50"
                                        class="w-16 rounded-lg border border-zinc-200 bg-white px-2 py-2 text-center text-lg font-semibold text-zinc-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <flux:button
                                        wire:click="incrementTicket({{ $ticket['id'] }})"
                                        size="sm"
                                        variant="primary"
                                        icon="plus"
                                        class="size-10 rounded-full"
                                    />
                                </div>
                            </div>

                            @if($tQty > 0)
                                <div class="mt-3 flex justify-end">
                                    <span class="text-sm font-semibold text-sky-600 dark:text-sky-400">
                                        Subtotal: Rp {{ number_format($ticket['price'] * $tQty, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 flex items-center justify-between">
                <flux:button wire:click="prevStep" variant="ghost" icon="arrow-left">
                    Kembali
                </flux:button>
                <div class="flex items-center gap-4">
                    @if($this->getTicketCount() > 0)
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ $this->getTicketCount() }} tiket dipilih
                        </span>
                    @endif
                    <flux:button wire:click="nextStep" variant="primary" icon-trailing="arrow-right" :disabled="$this->getTicketCount() === 0">
                        Pilih Add-on
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================== Step 3: Add-ons ======================== --}}
    @if($step === 3)
        <div>
            <div class="mb-6 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon name="cube" variant="mini" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Tambahan (Opsional)</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Tambahkan perlengkapan untuk pengalaman lebih menyenangkan</p>
                </div>
            </div>

            @if(empty($addons))
                <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="cube" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-4 text-zinc-500 dark:text-zinc-400">Belum ada add-on tersedia.</p>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($addons as $addon)
                        @php $aKey = "addon_{$addon['id']}"; $aQty = $addonQuantities[$aKey] ?? 0; @endphp
                        <div class="rounded-2xl border border-zinc-200 bg-white p-5 transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900
                            {{ $aQty > 0 ? 'ring-2 ring-amber-500/50' : '' }}">
                            <div class="mb-3">
                                <h3 class="font-bold text-zinc-900 dark:text-white">{{ $addon['name'] }}</h3>
                                @if($addon['description'])
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $addon['description'] }}</p>
                                @endif
                                <p class="mt-2 text-lg font-bold text-zinc-900 dark:text-white">
                                    Rp {{ number_format($addon['price'], 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <flux:button
                                        wire:click="decrementAddon({{ $addon['id'] }})"
                                        size="xs"
                                        variant="ghost"
                                        icon="minus"
                                        :disabled="$aQty === 0"
                                        class="size-8 rounded-full"
                                    />
                                    <input
                                        type="number"
                                        value="{{ $aQty }}"
                                        wire:change="setAddonQuantity({{ $addon['id'] }}, $event.target.value)"
                                        min="0"
                                        max="50"
                                        class="w-14 rounded-lg border border-zinc-200 bg-white px-2 py-1 text-center font-semibold text-zinc-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <flux:button
                                        wire:click="incrementAddon({{ $addon['id'] }})"
                                        size="xs"
                                        variant="primary"
                                        icon="plus"
                                        class="size-8 rounded-full"
                                    />
                                </div>
                                @if($aQty > 0)
                                    <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                                        Rp {{ number_format($addon['price'] * $aQty, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Running Subtotal -->
            <div class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Subtotal sementara</span>
                    <span class="text-lg font-bold text-zinc-900 dark:text-white">
                        Rp {{ number_format($this->subtotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <flux:button wire:click="prevStep" variant="ghost" icon="arrow-left">
                    Kembali
                </flux:button>
                <flux:button wire:click="nextStep" variant="primary" icon-trailing="arrow-right">
                    Lanjut ke Pembayaran
                </flux:button>
            </div>
        </div>
    @endif

    {{-- ======================== Step 4: Checkout ======================== --}}
    @if($step === 4)
        <form wire:submit="placeOrder">
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left: Form -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Customer Info -->
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Informasi Pemesan</h2>
                        <div class="space-y-4">
                            <flux:input wire:model="customerName" label="Nama Lengkap" placeholder="Masukkan nama lengkap" required />
                            <flux:input wire:model="customerEmail" label="Email" type="email" placeholder="email@contoh.com" description="E-tiket akan dikirim ke email ini" required />
                            <flux:input wire:model="customerPhone" label="No. Telepon" type="tel" placeholder="08xxxxxxxxxx" required />
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Metode Pembayaran</h2>
                        <flux:radio.group wire:model="paymentMethod" variant="cards">
                            <flux:radio value="bank_transfer" label="Transfer Bank" description="Upload bukti transfer untuk verifikasi admin" />
                            <flux:radio value="midtrans" label="Pembayaran Instan" description="Bayar cepat via QRIS, Virtual Account, atau e-Wallet" />
                        </flux:radio.group>
                    </div>

                    <!-- Voucher -->
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Kode Voucher</h2>
                        @if($appliedVoucher)
                            <div class="flex items-center justify-between rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                                <div>
                                    <span class="font-semibold text-green-700 dark:text-green-400">{{ $appliedVoucher['code'] }}</span>
                                    <span class="text-sm text-green-600 dark:text-green-500"> &mdash; {{ $appliedVoucher['name'] }}</span>
                                </div>
                                <flux:button wire:click="removeVoucher" size="xs" variant="ghost" icon="x-mark" />
                            </div>
                        @else
                            <div class="flex gap-2">
                                <flux:input wire:model="voucherCode" placeholder="Masukkan kode voucher" class="flex-1" />
                                <flux:button wire:click="applyVoucher" variant="ghost">Terapkan</flux:button>
                            </div>
                            @error('voucherCode')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div>
                    <div class="sticky top-24 rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Ringkasan Pesanan</h2>

                        <p class="mb-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            <flux:icon name="calendar-days" variant="mini" class="mr-1 inline size-4" />
                            {{ \Carbon\Carbon::parse($visitDate)->translatedFormat('l, d F Y') }}
                        </p>

                        <div class="space-y-2">
                            @foreach($this->selectedItems as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $item['name'] }} &times; {{ $item['quantity'] }}</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 space-y-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                                <span class="text-zinc-900 dark:text-white">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($discount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-green-600 dark:text-green-400">Diskon</span>
                                    <span class="text-green-600 dark:text-green-400">-Rp {{ number_format($discount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                <span class="text-lg font-semibold text-zinc-900 dark:text-white">Total</span>
                                <span class="text-lg font-bold text-zinc-900 dark:text-white">
                                    Rp {{ number_format($this->total, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <flux:button type="submit" variant="primary" class="mt-6 w-full" icon="check">
                            Pesan Sekarang
                        </flux:button>

                        <flux:button wire:click="prevStep" variant="ghost" class="mt-2 w-full" icon="arrow-left">
                            Kembali
                        </flux:button>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>

<?php

use App\Models\Addon;
use App\Models\TicketType;
use App\Services\CartService;
use App\Services\PricingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tiket - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
    public string $visitDate = '';

    public array $ticketTypes = [];

    public array $addons = [];

    public string $dayTypeLabel = '';

    public function mount(): void
    {
        $this->visitDate = now()->addDay()->format('Y-m-d');
        $this->loadProducts();
    }

    public function updatedVisitDate(): void
    {
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $pricingService = app(PricingService::class);
        $dayType = $pricingService->getDayType($this->visitDate);
        $this->dayTypeLabel = $dayType->label();

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

    public function addToCart(string $type, int $id, string $name, float $price): void
    {
        $cartService = app(CartService::class);
        $cartService->setVisitDate($this->visitDate);

        $cart = $cartService->getCart();
        $currentQty = 0;
        foreach ($cart['items'] as $item) {
            if ($item['type'] === $type && $item['id'] === $id) {
                $currentQty = $item['quantity'];
                break;
            }
        }

        $cartService->addItem($type, $id, $name, $price, $currentQty + 1);

        $this->dispatch('cart-updated');
        session()->flash('success', "{$name} ditambahkan ke keranjang.");
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">Beli Tiket</h1>
        <p class="mx-auto mt-3 max-w-2xl text-zinc-600 dark:text-zinc-400">
            Pilih tanggal kunjungan dan tiket yang diinginkan. Harga otomatis menyesuaikan berdasarkan hari.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <!-- Date Picker & Day Info -->
    <div class="mb-10 rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-end">
            <div class="w-full sm:w-auto">
                <flux:input
                    wire:model.live="visitDate"
                    type="date"
                    label="Tanggal Kunjungan"
                    :min="now()->addDay()->format('Y-m-d')"
                    class="max-w-xs"
                />
            </div>
            <div class="flex items-center gap-3">
                @php
                    $isWeekend = \Carbon\Carbon::parse($visitDate)->isWeekend();
                @endphp
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium {{ $isWeekend ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' }}">
                    <flux:icon name="calendar-days" variant="mini" class="size-4" />
                    {{ \Carbon\Carbon::parse($visitDate)->translatedFormat('l, d F Y') }}
                    &mdash; {{ $dayTypeLabel }}
                </span>
            </div>
        </div>
    </div>

    <!-- Ticket Types -->
    <div class="mb-12">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/30">
                <flux:icon name="ticket" variant="mini" class="size-5 text-sky-600 dark:text-sky-400" />
            </div>
            <div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Tiket Masuk</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pilih jenis tiket sesuai kebutuhan</p>
            </div>
        </div>

        @if(empty($ticketTypes))
            <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <flux:icon name="ticket" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-4 text-zinc-500 dark:text-zinc-400">Belum ada jenis tiket tersedia.</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($ticketTypes as $ticket)
                    <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white transition-shadow hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ $ticket['name'] }}</h3>
                            @if($ticket['description'])
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $ticket['description'] }}</p>
                            @endif

                            <!-- Current Price -->
                            <div class="mt-4">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Harga untuk tanggal terpilih</p>
                                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">
                                    Rp {{ number_format($ticket['price'], 0, ',', '.') }}
                                </p>
                            </div>

                            <!-- Price Comparison -->
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-800">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Hari Kerja</p>
                                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                        Rp {{ number_format($ticket['weekday_price'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-800">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Akhir Pekan</p>
                                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                        Rp {{ number_format($ticket['weekend_price'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                            <flux:button
                                wire:click="addToCart('ticket', {{ $ticket['id'] }}, '{{ addslashes($ticket['name']) }}', {{ $ticket['price'] }})"
                                variant="primary"
                                class="w-full"
                                icon="shopping-cart"
                            >
                                Tambah ke Keranjang
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Add-ons -->
    @if(!empty($addons))
        <div>
            <div class="mb-6 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon name="cube" variant="mini" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Add-on</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Tambahan untuk pengalaman lebih menyenangkan</p>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($addons as $addon)
                    <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white transition-shadow hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ $addon['name'] }}</h3>
                            @if($addon['description'])
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $addon['description'] }}</p>
                            @endif
                            <p class="mt-4 text-3xl font-bold text-zinc-900 dark:text-white">
                                Rp {{ number_format($addon['price'], 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                            <flux:button
                                wire:click="addToCart('addon', {{ $addon['id'] }}, '{{ addslashes($addon['name']) }}', {{ $addon['price'] }})"
                                variant="primary"
                                class="w-full"
                                icon="shopping-cart"
                            >
                                Tambah ke Keranjang
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<?php

use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Keranjang - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
    public array $cart = [];

    public function mount(): void
    {
        $this->loadCart();
    }

    #[On('cart-updated')]
    public function loadCart(): void
    {
        $cartService = app(CartService::class);
        $this->cart = $cartService->getCart();
    }

    public function updateQuantity(string $type, int $id, int $quantity): void
    {
        $cartService = app(CartService::class);

        if ($quantity <= 0) {
            $cartService->removeItem($type, $id);
        } else {
            $item = collect($this->cart['items'])->first(fn ($i) => $i['type'] === $type && $i['id'] === $id);
            if ($item) {
                $cartService->addItem($type, $id, $item['name'], $item['price'], $quantity);
            }
        }

        $this->loadCart();
    }

    public function removeItem(string $type, int $id): void
    {
        app(CartService::class)->removeItem($type, $id);
        $this->loadCart();
    }

    public function clearCart(): void
    {
        app(CartService::class)->clear();
        $this->loadCart();
    }

    public function getSubtotalProperty(): float
    {
        return app(CartService::class)->getSubtotal();
    }

    public function proceedToCheckout(): void
    {
        if (empty($this->cart['items'])) {
            session()->flash('error', 'Keranjang masih kosong.');
            return;
        }

        $this->redirect(route('checkout'), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold text-zinc-900 dark:text-white">Keranjang</h1>

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($cart['items']))
        <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon name="shopping-cart" class="mx-auto size-12 text-zinc-400" />
            <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Keranjang Kosong</h3>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">Silakan pilih tiket terlebih dahulu.</p>
            <flux:button :href="route('tiket.index')" variant="primary" class="mt-6" wire:navigate>
                Lihat Tiket
            </flux:button>
        </div>
    @else
        <div class="space-y-4">
            @if($cart['visit_date'])
                <div class="rounded-lg bg-blue-50 p-3 text-sm text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                    Tanggal kunjungan: <strong>{{ \Carbon\Carbon::parse($cart['visit_date'])->translatedFormat('l, d F Y') }}</strong>
                </div>
            @endif

            <!-- Cart Items -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                @foreach($cart['items'] as $index => $item)
                    <div class="flex items-center justify-between border-b border-zinc-100 p-4 last:border-b-0 dark:border-zinc-800">
                        <div class="flex-1">
                            <h4 class="font-medium text-zinc-900 dark:text-white">{{ $item['name'] }}</h4>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $item['type'] === 'ticket' ? 'Tiket' : 'Add-on' }} ·
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <flux:button
                                    wire:click="updateQuantity('{{ $item['type'] }}', {{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                    size="xs"
                                    variant="ghost"
                                    icon="minus"
                                />
                                <span class="w-8 text-center text-sm font-medium text-zinc-900 dark:text-white">{{ $item['quantity'] }}</span>
                                <flux:button
                                    wire:click="updateQuantity('{{ $item['type'] }}', {{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                    size="xs"
                                    variant="ghost"
                                    icon="plus"
                                />
                            </div>
                            <span class="w-28 text-right font-semibold text-zinc-900 dark:text-white">
                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                            </span>
                            <flux:button
                                wire:click="removeItem('{{ $item['type'] }}', {{ $item['id'] }})"
                                size="xs"
                                variant="ghost"
                                icon="trash"
                                class="text-red-500 hover:text-red-700"
                            />
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <span class="text-lg font-semibold text-zinc-900 dark:text-white">Subtotal</span>
                    <span class="text-lg font-bold text-zinc-900 dark:text-white">
                        Rp {{ number_format($this->subtotal, 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-4 flex gap-3">
                    <flux:button wire:click="clearCart" variant="ghost" class="flex-1">
                        Kosongkan
                    </flux:button>
                    <flux:button wire:click="proceedToCheckout" variant="primary" class="flex-1" icon="arrow-right">
                        Checkout
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

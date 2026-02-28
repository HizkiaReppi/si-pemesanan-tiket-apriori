<?php

use App\Actions\CreateOrder;
use App\Enums\DayType;
use App\Models\Addon;
use App\Models\TicketType;
use App\Services\PricingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Point of Sale')] #[Layout('layouts.app')] class extends Component {
    public string $visitDate = '';

    public string $customerName = '';

    public string $customerPhone = '';

    public string $customerEmail = '';

    public string $voucherCode = '';

    public string $paymentMethod = 'cash';

    public array $items = [];

    public float $discount = 0;

    public function mount(): void
    {
        $this->visitDate = now()->toDateString();
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $dayType = app(PricingService::class)->getDayType($this->visitDate);

        $tickets = TicketType::active()->ordered()->with(['prices' => fn ($q) => $q->where('day_type', $dayType)])->get();
        $addons = Addon::active()->ordered()->get();

        $this->items = [];

        foreach ($tickets as $ticket) {
            $price = $ticket->prices->first()?->price ?? 0;
            $this->items[] = [
                'type' => 'ticket',
                'id' => $ticket->id,
                'name' => $ticket->name . ' (' . $dayType->label() . ')',
                'price' => $price,
                'quantity' => 0,
            ];
        }

        foreach ($addons as $addon) {
            $this->items[] = [
                'type' => 'addon',
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => $addon->price,
                'quantity' => 0,
            ];
        }
    }

    public function updatedVisitDate(): void
    {
        $this->loadProducts();
    }

    public function increment(int $index): void
    {
        $this->items[$index]['quantity']++;
    }

    public function decrement(int $index): void
    {
        if ($this->items[$index]['quantity'] > 0) {
            $this->items[$index]['quantity']--;
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => $i['price'] * $i['quantity']);
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - $this->discount);
    }

    public function placeOrder(): void
    {
        $this->validate([
            'visitDate' => 'required|date|after_or_equal:today',
            'customerName' => 'required|string|max:255',
        ]);

        $hasItems = collect($this->items)->contains(fn ($i) => $i['quantity'] > 0);
        if (! $hasItems) {
            session()->flash('error', 'Tambahkan minimal 1 item.');
            return;
        }

        // Build cart array for CreateOrder
        $cartItems = collect($this->items)
            ->filter(fn ($item) => $item['quantity'] > 0)
            ->map(fn ($item) => [
                'type' => $item['type'],
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ])
            ->values()
            ->toArray();

        $cart = [
            'visit_date' => $this->visitDate,
            'items' => $cartItems,
        ];

        $selectedMethod = \App\Enums\PaymentMethod::from($this->paymentMethod);

        $action = app(CreateOrder::class);
        $order = $action->execute(
            cart: $cart,
            customer: [
                'name' => $this->customerName,
                'email' => $this->customerEmail ?: null,
                'phone' => $this->customerPhone ?: null,
            ],
            paymentMethod: $selectedMethod,
            userId: null,
        );

        if ($selectedMethod === \App\Enums\PaymentMethod::Cash) {
            // Mark as paid immediately for cash
            $order->update(['status' => \App\Enums\OrderStatus::Paid]);
            $order->payment?->update([
                'status' => \App\Enums\PaymentStatus::Paid,
                'paid_at' => now(),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            event(new \App\Events\OrderPaid($order));
            session()->flash('success', "Pesanan {$order->code} berhasil dibuat dan dibayar tunai!");
        } else {
            // Non-cash: order created, awaiting payment
            session()->flash('success', "Pesanan {$order->code} berhasil dibuat. Menunggu pembayaran {$selectedMethod->label()}.");
        }

        $this->redirect(route('kasir.pesanan'), navigate: true);
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->with([
            'subtotal' => $this->subtotal,
            'total' => $this->total,
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Point of Sale</flux:heading>

        @if(session('error'))
            <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Products -->
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:input wire:model.live="visitDate" label="Tanggal Kunjungan" type="date" />
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Pilih Item</h3>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($items as $index => $item)
                            <div class="flex items-center justify-between p-4">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item['name'] }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:button wire:click="decrement({{ $index }})" size="xs" variant="ghost" icon="minus" :disabled="$item['quantity'] <= 0" />
                                    <span class="w-8 text-center text-sm font-medium text-zinc-900 dark:text-white">{{ $item['quantity'] }}</span>
                                    <flux:button wire:click="increment({{ $index }})" size="xs" variant="ghost" icon="plus" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="space-y-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">Data Pelanggan</h3>
                    <div class="space-y-3">
                        <flux:input wire:model="customerName" label="Nama *" placeholder="Nama pelanggan" />
                        <flux:input wire:model="customerPhone" label="No. HP" placeholder="08xxxxxxxxxx" />
                        <flux:input wire:model="customerEmail" label="Email" type="email" placeholder="email@example.com" />
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">Ringkasan</h3>
                    @foreach($items as $item)
                        @if($item['quantity'] > 0)
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                                <span class="text-zinc-900 dark:text-white">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <div class="flex justify-between font-semibold text-zinc-900 dark:text-white">
                            <span>Total</span>
                            <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <flux:select wire:model="paymentMethod" label="Metode Pembayaran">
                            <flux:select.option value="cash">Tunai</flux:select.option>
                            <flux:select.option value="bank_transfer">Transfer Bank</flux:select.option>
                            <flux:select.option value="midtrans">Non-Tunai (Midtrans)</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:button wire:click="placeOrder" variant="primary" class="mt-4 w-full" :icon="$paymentMethod === 'cash' ? 'banknotes' : 'credit-card'">
                        {{ $paymentMethod === 'cash' ? 'Proses Pembayaran Tunai' : 'Buat Pesanan' }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>

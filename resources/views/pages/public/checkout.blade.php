<?php

use App\Actions\ApplyVoucher;
use App\Actions\CreateOrder;
use App\Enums\PaymentMethod;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Checkout - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $customerName = '';

    #[Validate('required|email|max:255')]
    public string $customerEmail = '';

    #[Validate('required|string|max:20')]
    public string $customerPhone = '';

    #[Validate('required|in:midtrans,bank_transfer,cash')]
    public string $paymentMethod = 'bank_transfer';

    public string $voucherCode = '';

    public ?array $appliedVoucher = null;

    public float $discount = 0;

    public array $cart = [];

    public float $subtotal = 0;

    public function mount(): void
    {
        $cartService = app(CartService::class);

        if (! $cartService->hasItems()) {
            $this->redirect(route('keranjang'), navigate: true);
            return;
        }

        $this->cart = $cartService->getCart();
        $this->subtotal = $cartService->getSubtotal();

        if (auth()->check()) {
            $this->customerName = auth()->user()->name;
            $this->customerEmail = auth()->user()->email;
            $this->customerPhone = auth()->user()->phone ?? '';
        }
    }

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

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - $this->discount);
    }

    public function placeOrder(): void
    {
        $this->validate();

        $cartService = app(CartService::class);

        if (! $cartService->hasItems()) {
            session()->flash('error', 'Keranjang kosong.');
            return;
        }

        $order = app(CreateOrder::class)->execute(
            cart: $cartService->getCart(),
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

        $cartService->clear();

        $this->redirect(route('pesanan.detail', $order), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold text-zinc-900 dark:text-white">Checkout</h1>

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="placeOrder">
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left: Customer Info + Payment -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Customer Information -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Informasi Pemesan</h2>
                    <div class="space-y-4">
                        <flux:input wire:model="customerName" label="Nama Lengkap" required />
                        <flux:input wire:model="customerEmail" label="Email" type="email" required />
                        <flux:input wire:model="customerPhone" label="No. Telepon" type="tel" required />
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Metode Pembayaran</h2>
                    <flux:radio.group wire:model="paymentMethod" variant="cards">
                        <flux:radio value="bank_transfer" label="Transfer Bank" description="Upload bukti transfer untuk verifikasi admin" />
                        <flux:radio value="midtrans" label="Midtrans" description="Bayar online via berbagai metode pembayaran" />
                    </flux:radio.group>
                </div>

                <!-- Voucher -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Kode Voucher</h2>
                    @if($appliedVoucher)
                        <div class="flex items-center justify-between rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                            <div>
                                <span class="font-semibold text-green-700 dark:text-green-400">{{ $appliedVoucher['code'] }}</span>
                                <span class="text-sm text-green-600 dark:text-green-500"> - {{ $appliedVoucher['name'] }}</span>
                            </div>
                            <flux:button wire:click="removeVoucher" size="xs" variant="ghost" icon="x" />
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
                <div class="sticky top-24 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Ringkasan Pesanan</h2>

                    @if($cart['visit_date'])
                        <p class="mb-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ \Carbon\Carbon::parse($cart['visit_date'])->translatedFormat('l, d F Y') }}
                        </p>
                    @endif

                    <div class="space-y-2">
                        @foreach($cart['items'] as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                                <span class="text-zinc-900 dark:text-white">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 space-y-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                            <span class="text-zinc-900 dark:text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">Diskon</span>
                                <span class="text-green-600 dark:text-green-400">-Rp {{ number_format($discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-zinc-100 pt-2 dark:border-zinc-800">
                            <span class="text-lg font-semibold text-zinc-900 dark:text-white">Total</span>
                            <span class="text-lg font-bold text-zinc-900 dark:text-white">
                                Rp {{ number_format($this->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <flux:button type="submit" variant="primary" class="mt-6 w-full" icon="check">
                        Pesan Sekarang
                    </flux:button>
                </div>
            </div>
        </div>
    </form>
</div>

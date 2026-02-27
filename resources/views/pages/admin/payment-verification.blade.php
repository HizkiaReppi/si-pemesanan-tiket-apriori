<?php

use App\Actions\VerifyBankTransfer;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Verifikasi Pembayaran')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public function approve(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        app(VerifyBankTransfer::class)->execute($payment, auth()->id(), approved: true);
        session()->flash('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        app(VerifyBankTransfer::class)->execute($payment, auth()->id(), approved: false);
        session()->flash('success', 'Pembayaran ditolak.');
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->with('payments', Payment::with('order')
            ->where('method', PaymentMethod::BankTransfer)
            ->where('status', PaymentStatus::Verifying)
            ->latest()
            ->paginate(15));
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Verifikasi Pembayaran</flux:heading>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse($payments as $payment)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $payment->order->code }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $payment->order->customer_name }} · {{ $payment->order->customer_email }}
                            </p>
                            <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </p>
                            @if($payment->bank_name)
                                <p class="text-sm text-zinc-500">Bank: {{ $payment->bank_name }} - {{ $payment->bank_account_number }}</p>
                            @endif
                            <p class="text-xs text-zinc-400">{{ $payment->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex gap-2">
                            @if($payment->bank_transfer_proof)
                                <flux:button href="{{ Storage::url($payment->bank_transfer_proof) }}" target="_blank" variant="ghost" size="sm" icon="image">
                                    Bukti
                                </flux:button>
                            @endif
                            <flux:button wire:click="approve({{ $payment->id }})" wire:confirm="Verifikasi pembayaran ini?" variant="primary" size="sm" icon="check">
                                Terima
                            </flux:button>
                            <flux:button wire:click="reject({{ $payment->id }})" wire:confirm="Tolak pembayaran ini?" variant="danger" size="sm" icon="x">
                                Tolak
                            </flux:button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="check-circle" class="mx-auto size-12 text-green-500" />
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Semua Terverifikasi</h3>
                    <p class="mt-2 text-zinc-600 dark:text-zinc-400">Tidak ada pembayaran yang menunggu verifikasi.</p>
                </div>
            @endforelse

            {{ $payments->links() }}
        </div>
    </div>
</div>

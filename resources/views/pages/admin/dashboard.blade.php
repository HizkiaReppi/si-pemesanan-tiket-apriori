<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\CheckIn;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Admin')] #[Layout('layouts.app')] class extends Component {
    public function rendering(\Illuminate\View\View $view): void
    {
        $today = now()->toDateString();

        $view->with([
            'todayOrders' => Order::whereDate('created_at', $today)->count(),
            'todayRevenue' => Order::whereDate('created_at', $today)
                ->whereIn('status', [OrderStatus::Paid, OrderStatus::Confirmed, OrderStatus::Completed])
                ->sum('total'),
            'todayCheckIns' => CheckIn::whereDate('scanned_at', $today)
                ->where('status', 'valid')
                ->count(),
            'pendingVerifications' => Order::where('status', OrderStatus::AwaitingPayment)
                ->whereHas('payment', fn ($q) => $q->where('status', 'verifying'))
                ->count(),
            'recentOrders' => Order::with('user')->latest()->take(5)->get(),
            'monthlyRevenue' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', [OrderStatus::Paid, OrderStatus::Confirmed, OrderStatus::Completed])
                ->sum('total'),
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Dashboard Admin</flux:heading>

        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pesanan Hari Ini</p>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $todayOrders }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pendapatan Hari Ini</p>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Check-in Hari Ini</p>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $todayCheckIns }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Menunggu Verifikasi</p>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $pendingVerifications }}</p>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan Bulan Ini</h3>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">
                Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
            </p>
        </div>

        <!-- Recent Orders -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 p-4 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Pesanan Terbaru</h3>
                <flux:button :href="route('admin.pesanan')" variant="ghost" size="sm" wire:navigate>
                    Lihat Semua
                </flux:button>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($recentOrders as $order)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $order->code }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $order->customer_name }} · {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-zinc-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                            <p class="text-sm" style="color: {{ $order->status->color() }}">{{ $order->status->label() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="p-4 text-sm text-zinc-500 dark:text-zinc-400">Belum ada pesanan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

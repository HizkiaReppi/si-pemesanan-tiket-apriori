<?php

use App\Models\CheckIn;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Kasir')] #[Layout('layouts.app')] class extends Component {
    public function rendering(\Illuminate\View\View $view): void
    {
        $today = now()->toDateString();

        $view->with([
            'todayOrders' => Order::whereDate('created_at', $today)->count(),
            'todayCashOrders' => Order::whereDate('created_at', $today)->where('payment_method', 'cash')->count(),
            'todayRevenue' => Order::whereDate('created_at', $today)->whereIn('status', ['paid', 'confirmed', 'completed'])->sum('total'),
            'todayCheckIns' => CheckIn::whereDate('created_at', $today)->count(),
            'pendingOrders' => Order::where('status', 'pending')->where('payment_method', 'cash')->latest()->take(5)->get(),
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Dashboard Kasir</flux:heading>

        <!-- Stats -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pesanan Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $todayOrders }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pesanan Tunai</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $todayCashOrders }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pendapatan Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Check-in Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $todayCheckIns }}</p>
            </div>
        </div>

        <!-- Pending Cash Orders -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Pesanan Tunai Menunggu</h3>
            </div>
            @forelse($pendingOrders as $order)
                <div class="flex items-center justify-between border-b border-zinc-100 p-4 last:border-b-0 dark:border-zinc-800">
                    <div>
                        <p class="font-mono text-sm text-zinc-900 dark:text-white">{{ $order->code }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->customer_name }} · {{ $order->visit_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                        <a href="{{ route('kasir.pesanan') }}" class="text-xs text-blue-600 hover:underline dark:text-blue-400">Proses</a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-zinc-400">Tidak ada pesanan yang menunggu.</div>
            @endforelse
        </div>

        <!-- Quick Actions -->
        <div class="flex gap-4">
            <flux:button href="{{ route('kasir.penjualan') }}" variant="primary" icon="plus">Buat Pesanan Baru</flux:button>
            <flux:button href="{{ route('kasir.pesanan') }}" variant="ghost" icon="list-bullet">Lihat Semua Pesanan</flux:button>
        </div>
    </div>
</div>

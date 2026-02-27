<?php

use App\Enums\OrderStatus;
use App\Models\CheckIn;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Kasir')] #[Layout('layouts.app')] class extends Component {
    public function rendering(\Illuminate\View\View $view): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $paidStatuses = [OrderStatus::Paid, OrderStatus::Confirmed, OrderStatus::Completed];

        $todayOrders = Order::whereDate('created_at', $today)->count();
        $yesterdayOrders = Order::whereDate('created_at', $yesterday)->count();

        $todayCashOrders = Order::whereDate('created_at', $today)->where('payment_method', 'cash')->count();
        $yesterdayCashOrders = Order::whereDate('created_at', $yesterday)->where('payment_method', 'cash')->count();

        $todayRevenue = Order::whereDate('created_at', $today)->whereIn('status', $paidStatuses)->sum('total');
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)->whereIn('status', $paidStatuses)->sum('total');

        $todayCheckIns = CheckIn::whereDate('created_at', $today)->count();
        $yesterdayCheckIns = CheckIn::whereDate('created_at', $yesterday)->count();

        // Today's popular items
        $todayPopular = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', $today)
            ->select('order_items.item_name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.item_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // Hourly breakdown today
        $hourlyOrders = Order::whereDate('created_at', $today)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $view->with([
            'todayOrders' => $todayOrders,
            'yesterdayOrders' => $yesterdayOrders,
            'todayCashOrders' => $todayCashOrders,
            'yesterdayCashOrders' => $yesterdayCashOrders,
            'todayRevenue' => $todayRevenue,
            'yesterdayRevenue' => $yesterdayRevenue,
            'todayCheckIns' => $todayCheckIns,
            'yesterdayCheckIns' => $yesterdayCheckIns,
            'pendingOrders' => Order::where('status', 'pending')->where('payment_method', 'cash')->latest()->take(5)->get(),
            'todayPopular' => $todayPopular,
            'hourlyOrders' => $hourlyOrders,
        ]);
    }
}; ?>

@php
    $calcTrend = function ($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    };
@endphp

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Dashboard Kasir</flux:heading>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <!-- Stats with Trends -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @php $orderTrend = $calcTrend($todayOrders, $yesterdayOrders); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pesanan Hari Ini</p>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/30">
                        <flux:icon name="shopping-bag" variant="mini" class="size-5 text-sky-600 dark:text-sky-400" />
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $todayOrders }}</p>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    @if($orderTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $orderTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $orderTrend }}%</span>
                    @endif
                    <span class="text-zinc-400 dark:text-zinc-500">vs kemarin</span>
                </div>
            </div>

            @php $cashTrend = $calcTrend($todayCashOrders, $yesterdayCashOrders); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pesanan Tunai</p>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="banknotes" variant="mini" class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $todayCashOrders }}</p>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    @if($cashTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $cashTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $cashTrend }}%</span>
                    @endif
                    <span class="text-zinc-400 dark:text-zinc-500">vs kemarin</span>
                </div>
            </div>

            @php $revTrend = $calcTrend($todayRevenue, $yesterdayRevenue); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan Hari Ini</p>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <flux:icon name="currency-dollar" variant="mini" class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    @if($revTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $revTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $revTrend }}%</span>
                    @endif
                    <span class="text-zinc-400 dark:text-zinc-500">vs kemarin</span>
                </div>
            </div>

            @php $ciTrend = $calcTrend($todayCheckIns, $yesterdayCheckIns); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Check-in Hari Ini</p>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                        <flux:icon name="qr-code" variant="mini" class="size-5 text-violet-600 dark:text-violet-400" />
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $todayCheckIns }}</p>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    @if($ciTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $ciTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $ciTrend }}%</span>
                    @endif
                    <span class="text-zinc-400 dark:text-zinc-500">vs kemarin</span>
                </div>
            </div>
        </div>

        <!-- Popular Items + Hourly Orders Row -->
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Popular Items Today -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Item Populer Hari Ini</h3>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($todayPopular as $item)
                        @php $maxQty = $todayPopular->max('total_qty'); @endphp
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item->item_name }}</span>
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">{{ number_format($item->total_qty) }}x</span>
                            </div>
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-emerald-500" style="width: {{ $maxQty > 0 ? ($item->total_qty / $maxQty) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-zinc-500 dark:text-zinc-400">Belum ada pesanan hari ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Hourly Orders Today -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Pesanan per Jam</h3>
                </div>
                <div class="p-4">
                    @php
                        $hours = range(7, 18);
                        $maxHourly = count($hourlyOrders) > 0 ? max($hourlyOrders) : 0;
                    @endphp
                    <div class="flex items-end gap-1.5" style="height: 140px;">
                        @foreach($hours as $hour)
                            @php $count = $hourlyOrders[$hour] ?? 0; @endphp
                            <div class="flex flex-1 flex-col items-center gap-1">
                                @if($count > 0)
                                    <span class="text-[10px] font-medium text-zinc-600 dark:text-zinc-400">{{ $count }}</span>
                                @endif
                                <div class="w-full rounded-t-md transition-all {{ $count > 0 ? 'bg-gradient-to-t from-sky-500 to-sky-400 dark:from-sky-600 dark:to-sky-500' : 'bg-zinc-100 dark:bg-zinc-700' }}" style="height: {{ $maxHourly > 0 ? max(4, ($count / $maxHourly) * 110) : 4 }}px;"></div>
                                <span class="text-[10px] text-zinc-400">{{ $hour }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-center text-xs text-zinc-400">Jam operasional (07:00 - 18:00)</p>
                </div>
            </div>
        </div>

        <!-- Pending Cash Orders -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 p-4 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Pesanan Tunai Menunggu</h3>
                <flux:button href="{{ route('kasir.pesanan') }}" variant="ghost" size="sm" wire:navigate>Lihat Semua</flux:button>
            </div>
            @forelse($pendingOrders as $order)
                <div class="flex items-center justify-between border-b border-zinc-100 p-4 last:border-b-0 dark:border-zinc-800">
                    <div>
                        <p class="font-mono text-sm text-zinc-900 dark:text-white">{{ $order->code }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->customer_name }} · {{ $order->visit_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                        <a href="{{ route('kasir.pesanan') }}" class="text-xs text-sky-600 hover:underline dark:text-sky-400" wire:navigate>Proses</a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-zinc-400">Tidak ada pesanan yang menunggu.</div>
            @endforelse
        </div>

        <!-- Quick Actions -->
        <div class="flex gap-4">
            <flux:button href="{{ route('kasir.penjualan') }}" variant="primary" icon="plus" wire:navigate>Buat Pesanan Baru</flux:button>
            <flux:button href="{{ route('kasir.pesanan') }}" variant="ghost" icon="list-bullet" wire:navigate>Lihat Semua Pesanan</flux:button>
        </div>
    </div>
</div>

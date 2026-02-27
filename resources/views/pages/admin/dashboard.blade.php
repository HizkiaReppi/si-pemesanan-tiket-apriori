<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CheckIn;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Admin')] #[Layout('layouts.app')] class extends Component {
    public function rendering(\Illuminate\View\View $view): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $paidStatuses = [OrderStatus::Paid, OrderStatus::Confirmed, OrderStatus::Completed];

        $todayOrders = Order::whereDate('created_at', $today)->count();
        $yesterdayOrders = Order::whereDate('created_at', $yesterday)->count();

        $todayRevenue = Order::whereDate('created_at', $today)->whereIn('status', $paidStatuses)->sum('total');
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)->whereIn('status', $paidStatuses)->sum('total');

        $todayCheckIns = CheckIn::whereDate('scanned_at', $today)->where('status', 'valid')->count();
        $yesterdayCheckIns = CheckIn::whereDate('scanned_at', $yesterday)->where('status', 'valid')->count();

        $pendingVerifications = Order::where('status', OrderStatus::AwaitingPayment)
            ->whereHas('payment', fn ($q) => $q->where('status', 'verifying'))
            ->count();

        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', $paidStatuses)
            ->sum('total');

        $lastMonthRevenue = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereIn('status', $paidStatuses)
            ->sum('total');

        // Last 7 days revenue chart
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) use ($paidStatuses) {
            $date = now()->subDays($daysAgo);

            return [
                'date' => $date->translatedFormat('D'),
                'revenue' => (float) Order::whereIn('status', $paidStatuses)
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('total'),
                'orders' => Order::whereDate('created_at', $date->toDateString())->count(),
            ];
        });

        // Popular ticket types (this month)
        $popularTickets = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', collect($paidStatuses)->map(fn ($s) => $s->value))
            ->where('orders.created_at', '>=', now()->startOfMonth())
            ->select('order_items.item_name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->groupBy('order_items.item_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // Payment method distribution (this month)
        $paymentMethods = Order::where('created_at', '>=', now()->startOfMonth())
            ->whereIn('status', $paidStatuses)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('payment_method')
            ->get();

        // Order status distribution
        $statusDistribution = Order::where('created_at', '>=', now()->startOfMonth())
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $totalCustomers = User::where('role', 'customer')->count();

        $view->with([
            'todayOrders' => $todayOrders,
            'yesterdayOrders' => $yesterdayOrders,
            'todayRevenue' => $todayRevenue,
            'yesterdayRevenue' => $yesterdayRevenue,
            'todayCheckIns' => $todayCheckIns,
            'yesterdayCheckIns' => $yesterdayCheckIns,
            'pendingVerifications' => $pendingVerifications,
            'monthlyRevenue' => $monthlyRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'recentOrders' => Order::with('user')->latest()->take(5)->get(),
            'last7Days' => $last7Days,
            'popularTickets' => $popularTickets,
            'paymentMethods' => $paymentMethods,
            'statusDistribution' => $statusDistribution,
            'totalCustomers' => $totalCustomers,
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
            <flux:heading size="xl">Dashboard Admin</flux:heading>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <!-- Stats Cards with Trends -->
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

            @php $revTrend = $calcTrend($todayRevenue, $yesterdayRevenue); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan Hari Ini</p>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="banknotes" variant="mini" class="size-5 text-green-600 dark:text-green-400" />
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

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Verifikasi Menunggu</p>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <flux:icon name="clock" variant="mini" class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $pendingVerifications }}</p>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    <flux:icon name="users" variant="mini" class="size-4 text-zinc-400" />
                    <span class="text-zinc-400 dark:text-zinc-500">{{ number_format($totalCustomers) }} total pelanggan</span>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue + 7 Day Chart Row -->
        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Monthly Revenue Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan Bulan Ini</h3>
                <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                    Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
                </p>
                @php $monthTrend = $calcTrend($monthlyRevenue, $lastMonthRevenue); @endphp
                <div class="mt-2 flex items-center gap-1 text-xs">
                    @if($monthTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $monthTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $monthTrend }}%</span>
                    @endif
                    <span class="text-zinc-400 dark:text-zinc-500">vs bulan lalu</span>
                </div>

                <!-- Order Status Distribution -->
                <div class="mt-6 space-y-2">
                    <h4 class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status Pesanan</h4>
                    @foreach($statusDistribution as $sd)
                        @php $statusEnum = OrderStatus::from($sd->status instanceof OrderStatus ? $sd->status->value : $sd->status); @endphp
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span class="inline-block size-2 rounded-full" style="background-color: {{ $statusEnum->color() }}"></span>
                                {{ $statusEnum->label() }}
                            </span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $sd->count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 7 Day Revenue Chart -->
            <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan 7 Hari Terakhir</h3>
                @php $maxRevenue = $last7Days->max('revenue'); @endphp
                <div class="mt-4 flex items-end gap-3" style="height: 180px;">
                    @foreach($last7Days as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                                {{ $day['orders'] }}
                            </span>
                            <span class="text-[10px] text-zinc-400">
                                Rp {{ number_format($day['revenue'] / 1000, 0) }}K
                            </span>
                            <div class="w-full rounded-t-lg bg-gradient-to-t from-sky-500 to-sky-400 transition-all dark:from-sky-600 dark:to-sky-500" style="height: {{ $maxRevenue > 0 ? max(8, ($day['revenue'] / $maxRevenue) * 140) : 8 }}px;"></div>
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $day['date'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center justify-center gap-4 text-xs text-zinc-400">
                    <span class="flex items-center gap-1">
                        <span class="inline-block size-2 rounded-full bg-sky-500"></span>
                        Pendapatan
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="font-medium text-zinc-600 dark:text-zinc-300">Angka atas</span>
                        = Jumlah pesanan
                    </span>
                </div>
            </div>
        </div>

        <!-- Popular Tickets + Payment Methods Row -->
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Popular Tickets -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Tiket Populer Bulan Ini</h3>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($popularTickets as $ticket)
                        @php $maxQty = $popularTickets->max('total_qty'); @endphp
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $ticket->item_name }}</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($ticket->total_qty) }} tiket</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-emerald-500" style="width: {{ $maxQty > 0 ? ($ticket->total_qty / $maxQty) * 100 : 0 }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-zinc-400">Rp {{ number_format($ticket->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-zinc-500 dark:text-zinc-400">Belum ada data tiket.</p>
                    @endforelse
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Metode Pembayaran</h3>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @php
                        $totalPayments = $paymentMethods->sum('count');
                        $methodColors = ['cash' => 'green', 'bank_transfer' => 'amber', 'midtrans' => 'sky'];
                        $methodLabels = ['cash' => 'Tunai', 'bank_transfer' => 'Transfer Bank', 'midtrans' => 'Midtrans'];
                    @endphp
                    @forelse($paymentMethods as $pm)
                        @php
                            $methodKey = $pm->payment_method instanceof \App\Enums\PaymentMethod ? $pm->payment_method->value : $pm->payment_method;
                            $color = $methodColors[$methodKey] ?? 'zinc';
                            $label = $methodLabels[$methodKey] ?? $methodKey;
                            $pct = $totalPayments > 0 ? round(($pm->count / $totalPayments) * 100) : 0;
                        @endphp
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    <span class="inline-block size-3 rounded-full bg-{{ $color }}-500"></span>
                                    {{ $label }}
                                </span>
                                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $pct }}%</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div class="h-full rounded-full bg-{{ $color }}-500" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-zinc-400">{{ $pm->count }} pesanan · Rp {{ number_format($pm->revenue, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-zinc-500 dark:text-zinc-400">Belum ada data pembayaran.</p>
                    @endforelse
                </div>
            </div>
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

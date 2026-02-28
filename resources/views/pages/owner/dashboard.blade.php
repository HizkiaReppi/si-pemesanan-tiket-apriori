<?php

use App\Enums\OrderStatus;
use App\Models\CheckIn;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Pemilik')] #[Layout('layouts.app')] class extends Component {
    public function rendering(\Illuminate\View\View $view): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth();
        $paidStatuses = [OrderStatus::Paid, OrderStatus::Confirmed, OrderStatus::Completed];

        $monthlyRevenue = Order::whereIn('status', $paidStatuses)
            ->where('created_at', '>=', $thisMonth)
            ->sum('total');

        $lastMonthRevenue = Order::whereIn('status', $paidStatuses)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->sum('total');

        $monthlyOrders = Order::where('created_at', '>=', $thisMonth)->count();
        $lastMonthOrders = Order::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        $todayRevenue = Order::whereIn('status', $paidStatuses)->whereDate('created_at', $today)->sum('total');
        $yesterdayRevenue = Order::whereIn('status', $paidStatuses)->whereDate('created_at', $yesterday)->sum('total');

        $todayCheckIns = CheckIn::whereDate('created_at', $today)->count();
        $yesterdayCheckIns = CheckIn::whereDate('created_at', $yesterday)->count();

        $totalCustomers = User::where('role', 'customer')->count();
        $newCustomersThisMonth = User::where('role', 'customer')->where('created_at', '>=', $thisMonth)->count();

        // Last 7 days revenue
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

        // Top selling ticket types this month
        $topTickets = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', collect($paidStatuses)->map(fn ($s) => $s->value))
            ->where('orders.created_at', '>=', $thisMonth)
            ->select('order_items.item_name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->groupBy('order_items.item_name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // Payment method breakdown
        $paymentMethods = Order::where('created_at', '>=', $thisMonth)
            ->whereIn('status', $paidStatuses)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('payment_method')
            ->get();

        // Monthly trend (last 6 months)
        $monthlyTrend = collect(range(5, 0))->map(function ($monthsAgo) use ($paidStatuses) {
            $month = now()->subMonths($monthsAgo);

            return [
                'month' => $month->translatedFormat('M'),
                'revenue' => (float) Order::whereIn('status', $paidStatuses)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('total'),
                'orders' => Order::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        });

        $view->with([
            'monthlyRevenue' => $monthlyRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'monthlyOrders' => $monthlyOrders,
            'lastMonthOrders' => $lastMonthOrders,
            'todayRevenue' => $todayRevenue,
            'yesterdayRevenue' => $yesterdayRevenue,
            'todayCheckIns' => $todayCheckIns,
            'yesterdayCheckIns' => $yesterdayCheckIns,
            'totalCustomers' => $totalCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'last7Days' => $last7Days,
            'topTickets' => $topTickets,
            'paymentMethods' => $paymentMethods,
            'monthlyTrend' => $monthlyTrend,
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
            <flux:heading size="xl">Dashboard Pemilik</flux:heading>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <!-- Summary Stats -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            @php $mRevTrend = $calcTrend($monthlyRevenue, $lastMonthRevenue); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan Bulan Ini</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="banknotes" variant="mini" class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
                <div class="mt-1 flex items-center gap-1 text-xs">
                    @if($mRevTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $mRevTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $mRevTrend }}%</span>
                    @endif
                    <span class="text-zinc-400">vs bulan lalu</span>
                </div>
            </div>

            @php $mOrdTrend = $calcTrend($monthlyOrders, $lastMonthOrders); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pesanan Bulan Ini</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/30">
                        <flux:icon name="shopping-bag" variant="mini" class="size-5 text-sky-600 dark:text-sky-400" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($monthlyOrders) }}</p>
                <div class="mt-1 flex items-center gap-1 text-xs">
                    @if($mOrdTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $mOrdTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $mOrdTrend }}%</span>
                    @endif
                    <span class="text-zinc-400">vs bulan lalu</span>
                </div>
            </div>

            @php $dRevTrend = $calcTrend($todayRevenue, $yesterdayRevenue); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan Hari Ini</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <flux:icon name="currency-dollar" variant="mini" class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                <div class="mt-1 flex items-center gap-1 text-xs">
                    @if($dRevTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $dRevTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $dRevTrend }}%</span>
                    @endif
                    <span class="text-zinc-400">vs kemarin</span>
                </div>
            </div>

            @php $ciTrend = $calcTrend($todayCheckIns, $yesterdayCheckIns); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Check-in Hari Ini</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                        <flux:icon name="qr-code" variant="mini" class="size-5 text-violet-600 dark:text-violet-400" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">{{ $todayCheckIns }}</p>
                <div class="mt-1 flex items-center gap-1 text-xs">
                    @if($ciTrend >= 0)
                        <flux:icon name="arrow-trending-up" variant="mini" class="size-4 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $ciTrend }}%</span>
                    @else
                        <flux:icon name="arrow-trending-down" variant="mini" class="size-4 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">{{ $ciTrend }}%</span>
                    @endif
                    <span class="text-zinc-400">vs kemarin</span>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Pelanggan</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-rose-100 dark:bg-rose-900/30">
                        <flux:icon name="users" variant="mini" class="size-5 text-rose-600 dark:text-rose-400" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($totalCustomers) }}</p>
                <div class="mt-1 flex items-center gap-1 text-xs">
                    <flux:icon name="user-plus" variant="mini" class="size-4 text-green-500" />
                    <span class="text-green-600 dark:text-green-400">+{{ $newCustomersThisMonth }}</span>
                    <span class="text-zinc-400">bulan ini</span>
                </div>
            </div>
        </div>

        <!-- Charts Row: 7 Days + Monthly Trend -->
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Last 7 Days -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendapatan 7 Hari Terakhir</h3>
                @php $maxRevenue = $last7Days->max('revenue'); @endphp
                <div class="mt-4 flex items-end gap-3" style="height: 160px;">
                    @foreach($last7Days as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-[10px] font-semibold text-zinc-600 dark:text-zinc-400">{{ $day['orders'] }}</span>
                            <span class="text-[10px] text-zinc-400">{{ number_format($day['revenue'] / 1000, 0) }}K</span>
                            <div class="w-full rounded-t-lg bg-linear-to-t from-sky-500 to-sky-400 dark:from-sky-600 dark:to-sky-500" style="height: {{ $maxRevenue > 0 ? max(6, ($day['revenue'] / $maxRevenue) * 120) : 6 }}px;"></div>
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $day['date'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Monthly Trend (6 months) -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Tren Pendapatan 6 Bulan</h3>
                @php $maxMonthly = $monthlyTrend->max('revenue'); @endphp
                <div class="mt-4 flex items-end gap-3" style="height: 160px;">
                    @foreach($monthlyTrend as $m)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-[10px] font-semibold text-zinc-600 dark:text-zinc-400">{{ $m['orders'] }}</span>
                            <span class="text-[10px] text-zinc-400">{{ number_format($m['revenue'] / 1000000, 1) }}Jt</span>
                            <div class="w-full rounded-t-lg bg-linear-to-t from-emerald-500 to-emerald-400 dark:from-emerald-600 dark:to-emerald-500" style="height: {{ $maxMonthly > 0 ? max(6, ($m['revenue'] / $maxMonthly) * 120) : 6 }}px;"></div>
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $m['month'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Tickets + Payment Methods Row -->
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Top Tickets -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Tiket Terlaris Bulan Ini</h3>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($topTickets as $i => $ticket)
                        <div class="flex items-center gap-3 p-4">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $i === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' }} text-sm font-bold">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white truncate">{{ $ticket->item_name }}</p>
                                <p class="text-xs text-zinc-400">{{ number_format($ticket->total_qty) }} tiket</p>
                            </div>
                            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Rp {{ number_format($ticket->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-zinc-500 dark:text-zinc-400">Belum ada data tiket.</p>
                    @endforelse
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Distribusi Pembayaran</h3>
                </div>
                @php
                    $totalPayments = $paymentMethods->sum('count');
                    $methodColors = ['cash' => 'green', 'bank_transfer' => 'amber', 'midtrans' => 'sky'];
                    $methodLabels = ['cash' => 'Tunai', 'bank_transfer' => 'Transfer Bank', 'midtrans' => 'Midtrans'];
                @endphp
                <div class="p-6">
                    {{-- Stacked bar --}}
                    @if($totalPayments > 0)
                        <div class="flex h-4 w-full overflow-hidden rounded-full">
                            @foreach($paymentMethods as $pm)
                                @php
                                    $methodKey = $pm->payment_method instanceof \App\Enums\PaymentMethod ? $pm->payment_method->value : $pm->payment_method;
                                    $color = $methodColors[$methodKey] ?? 'zinc';
                                    $pct = round(($pm->count / $totalPayments) * 100);
                                @endphp
                                <div class="h-full bg-{{ $color }}-500" style="width: {{ $pct }}%" title="{{ $methodLabels[$methodKey] ?? $methodKey }}: {{ $pct }}%"></div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-4 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700"></div>
                    @endif

                    <div class="mt-4 space-y-3">
                        @foreach($paymentMethods as $pm)
                            @php
                                $methodKey = $pm->payment_method instanceof \App\Enums\PaymentMethod ? $pm->payment_method->value : $pm->payment_method;
                                $color = $methodColors[$methodKey] ?? 'zinc';
                                $label = $methodLabels[$methodKey] ?? $methodKey;
                                $pct = $totalPayments > 0 ? round(($pm->count / $totalPayments) * 100) : 0;
                            @endphp
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 text-zinc-700 dark:text-zinc-300">
                                    <span class="inline-block size-3 rounded-full bg-{{ $color }}-500"></span>
                                    {{ $label }}
                                </span>
                                <span class="text-zinc-500 dark:text-zinc-400">
                                    {{ $pm->count }} pesanan ({{ $pct }}%) · Rp {{ number_format($pm->revenue, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="flex gap-4">
            <flux:button href="{{ route('pemilik.laporan') }}" variant="primary" icon="chart-bar" wire:navigate>Laporan Lengkap</flux:button>
            <flux:button href="{{ route('pemilik.apriori') }}" variant="ghost" icon="cpu-chip" wire:navigate>Analisis Apriori</flux:button>
        </div>
    </div>
</div>

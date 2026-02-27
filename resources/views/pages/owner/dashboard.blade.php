<?php

use App\Models\CheckIn;
use App\Models\Order;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Pemilik')] #[Layout('layouts.app')] class extends Component {
    public function rendering(\Illuminate\View\View $view): void
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth();

        $monthlyRevenue = Order::whereIn('status', ['paid', 'confirmed', 'completed'])
            ->where('created_at', '>=', $thisMonth)
            ->sum('total');

        $monthlyOrders = Order::where('created_at', '>=', $thisMonth)->count();
        $todayRevenue = Order::whereIn('status', ['paid', 'confirmed', 'completed'])->whereDate('created_at', $today)->sum('total');
        $todayCheckIns = CheckIn::whereDate('created_at', $today)->count();
        $totalCustomers = User::where('role', 'customer')->count();

        // Last 7 days revenue
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'date' => $date->format('d/m'),
                'revenue' => Order::whereIn('status', ['paid', 'confirmed', 'completed'])
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('total'),
                'orders' => Order::whereDate('created_at', $date->toDateString())->count(),
            ];
        });

        $view->with([
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyOrders' => $monthlyOrders,
            'todayRevenue' => $todayRevenue,
            'todayCheckIns' => $todayCheckIns,
            'totalCustomers' => $totalCustomers,
            'last7Days' => $last7Days,
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Dashboard Pemilik</flux:heading>

        <!-- Summary -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pendapatan Bulan Ini</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pesanan Bulan Ini</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($monthlyOrders) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pendapatan Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Check-in Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $todayCheckIns }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Pelanggan</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalCustomers }}</p>
            </div>
        </div>

        <!-- Last 7 Days -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Pendapatan 7 Hari Terakhir</h3>
            </div>
            <div class="p-4">
                @php $maxRevenue = $last7Days->max('revenue'); @endphp
                <div class="flex items-end gap-2" style="height: 160px;">
                    @foreach($last7Days as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                Rp {{ number_format($day['revenue'] / 1000, 0) }}K
                            </span>
                            <div class="w-full rounded-t bg-blue-500" style="height: {{ $maxRevenue > 0 ? max(4, ($day['revenue'] / $maxRevenue) * 140) : 4 }}px;"></div>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $day['date'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="flex gap-4">
            <flux:button href="{{ route('pemilik.laporan') }}" variant="primary" icon="chart-bar">Laporan Lengkap</flux:button>
            <flux:button href="{{ route('pemilik.apriori') }}" variant="ghost" icon="cpu-chip">Analisis Apriori</flux:button>
        </div>
    </div>
</div>

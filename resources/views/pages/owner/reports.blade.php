<?php

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Laporan Pemilik')] #[Layout('layouts.app')] class extends Component {
    public string $startDate = '';

    public string $endDate = '';

    public string $period = 'month';

    public function mount(): void
    {
        $this->endDate = now()->toDateString();
        $this->startDate = now()->subMonth()->toDateString();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        match ($period) {
            'week' => $this->startDate = now()->subWeek()->toDateString(),
            'month' => $this->startDate = now()->subMonth()->toDateString(),
            'quarter' => $this->startDate = now()->subQuarter()->toDateString(),
            'year' => $this->startDate = now()->subYear()->toDateString(),
            default => null,
        };
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $paidStatuses = ['paid', 'confirmed', 'completed'];

        $query = Order::query()
            ->whereIn('status', $paidStatuses)
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate));

        $totalRevenue = (clone $query)->sum('total');
        $totalOrders = (clone $query)->count();
        $totalDiscount = (clone $query)->sum('discount');
        $avgOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // By product
        $productBreakdown = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->whereIn('status', $paidStatuses)
                ->when($this->startDate, fn ($q2) => $q2->whereDate('created_at', '>=', $this->startDate))
                ->when($this->endDate, fn ($q2) => $q2->whereDate('created_at', '<=', $this->endDate)))
            ->selectRaw('item_name, SUM(quantity) as qty, SUM(subtotal) as revenue')
            ->groupBy('item_name')
            ->orderByDesc('revenue')
            ->get();

        // By day type
        $dayTypeBreakdown = (clone $query)
            ->selectRaw('day_type, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('day_type')
            ->get();

        // By payment method
        $paymentBreakdown = (clone $query)
            ->selectRaw('payment_method, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get();

        $view->with([
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'totalDiscount' => $totalDiscount,
            'avgOrder' => $avgOrder,
            'productBreakdown' => $productBreakdown,
            'dayTypeBreakdown' => $dayTypeBreakdown,
            'paymentBreakdown' => $paymentBreakdown,
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Laporan Penjualan</flux:heading>

        <!-- Period Selector & Date Range -->
        <div class="flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex gap-2">
                @foreach(['week' => '1 Minggu', 'month' => '1 Bulan', 'quarter' => '3 Bulan', 'year' => '1 Tahun'] as $key => $label)
                    <flux:button wire:click="setPeriod('{{ $key }}')" :variant="$period === $key ? 'primary' : 'ghost'" size="sm">{{ $label }}</flux:button>
                @endforeach
            </div>
            <flux:input wire:model.live="startDate" label="Dari" type="date" />
            <flux:input wire:model.live="endDate" label="Sampai" type="date" />
        </div>

        <!-- Summary -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Pendapatan</p>
                <p class="mt-1 text-2xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Pesanan</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($totalOrders) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Rata-rata Pesanan</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($avgOrder, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Diskon</p>
                <p class="mt-1 text-2xl font-bold text-red-500">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Product Breakdown -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Penjualan per Produk</h3>
                </div>
                <div class="p-4">
                    @forelse($productBreakdown as $item)
                        <div class="mb-3 last:mb-0">
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-zinc-900 dark:text-white">{{ $item->item_name }}</span>
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $item->qty }} pcs · Rp {{ number_format($item->revenue, 0, ',', '.') }}</span>
                            </div>
                            @php $maxRev = $productBreakdown->max('revenue'); @endphp
                            <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ $maxRev > 0 ? ($item->revenue / $maxRev) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-zinc-400">Tidak ada data.</p>
                    @endforelse
                </div>
            </div>

            <!-- Day Type & Payment -->
            <div class="space-y-6">
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Tipe Hari</h3>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($dayTypeBreakdown as $dt)
                            <div class="flex items-center justify-between p-4">
                                <span class="text-sm text-zinc-900 dark:text-white">{{ \App\Enums\DayType::tryFrom($dt->day_type)?->label() ?? '-' }}</span>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Rp {{ number_format($dt->revenue, 0, ',', '.') }}</p>
                                    <p class="text-xs text-zinc-400">{{ $dt->orders }} pesanan</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Metode Pembayaran</h3>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($paymentBreakdown as $pm)
                            <div class="flex items-center justify-between p-4">
                                <span class="text-sm text-zinc-900 dark:text-white">{{ \App\Enums\PaymentMethod::tryFrom($pm->payment_method)?->label() ?? '-' }}</span>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Rp {{ number_format($pm->revenue, 0, ',', '.') }}</p>
                                    <p class="text-xs text-zinc-400">{{ $pm->orders }} pesanan</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

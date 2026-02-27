<?php

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Laporan')] #[Layout('layouts.app')] class extends Component {
    public string $period = 'month';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->endDate = now()->toDateString();
        $this->startDate = now()->subMonth()->toDateString();
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $query = Order::query()
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate));

        $totalRevenue = (clone $query)->sum('total');
        $totalOrders = (clone $query)->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalTickets = OrderItem::whereHas('order', fn ($q) => $q->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->when($this->startDate, fn ($q2) => $q2->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q2) => $q2->whereDate('created_at', '<=', $this->endDate))
        )->sum('quantity');

        // Daily revenue
        $dailyRevenue = (clone $query)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by ticket type
        $ticketBreakdown = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['paid', 'confirmed', 'completed'])
                ->when($this->startDate, fn ($q2) => $q2->whereDate('created_at', '>=', $this->startDate))
                ->when($this->endDate, fn ($q2) => $q2->whereDate('created_at', '<=', $this->endDate))
            )
            ->selectRaw('item_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('item_name')
            ->orderByDesc('total_revenue')
            ->get();

        // Revenue by payment method
        $paymentBreakdown = (clone $query)
            ->selectRaw('payment_method, COUNT(*) as total_orders, SUM(total) as total_revenue')
            ->groupBy('payment_method')
            ->get();

        // Day type breakdown
        $dayTypeBreakdown = (clone $query)
            ->selectRaw('day_type, COUNT(*) as total_orders, SUM(total) as total_revenue')
            ->groupBy('day_type')
            ->get();

        $view->with([
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'totalTickets' => $totalTickets,
            'dailyRevenue' => $dailyRevenue,
            'ticketBreakdown' => $ticketBreakdown,
            'paymentBreakdown' => $paymentBreakdown,
            'dayTypeBreakdown' => $dayTypeBreakdown,
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Laporan Penjualan</flux:heading>

        <!-- Date Range Filter -->
        <div class="flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:input wire:model.live="startDate" label="Dari Tanggal" type="date" />
            <flux:input wire:model.live="endDate" label="Sampai Tanggal" type="date" />
        </div>

        <!-- Summary Cards -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Pendapatan</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Pesanan</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($totalOrders) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Rata-rata Pesanan</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Tiket Terjual</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($totalTickets) }}</p>
            </div>
        </div>

        <!-- Daily Revenue Table -->
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Pendapatan Harian</h3>
            </div>
            <div class="max-h-96 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Tanggal</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Pesanan</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($dailyRevenue as $day)
                            <tr>
                                <td class="px-4 py-3 text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right text-zinc-600 dark:text-zinc-400">{{ $day->orders }}</td>
                                <td class="px-4 py-3 text-right font-medium text-zinc-900 dark:text-white">Rp {{ number_format($day->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-zinc-400">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Ticket Breakdown -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Penjualan per Produk</h3>
                </div>
                <div class="p-4">
                    @forelse($ticketBreakdown as $item)
                        <div class="mb-3 last:mb-0">
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-zinc-900 dark:text-white">{{ $item->item_name }}</span>
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $item->total_qty }} pcs · Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                            </div>
                            @php $maxRevenue = $ticketBreakdown->max('total_revenue'); @endphp
                            <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ $maxRevenue > 0 ? ($item->total_revenue / $maxRevenue) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-zinc-400">Tidak ada data.</p>
                    @endforelse
                </div>
            </div>

            <!-- Payment & Day Type -->
            <div class="space-y-6">
                <!-- Payment Method -->
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Metode Pembayaran</h3>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($paymentBreakdown as $pm)
                            <div class="flex items-center justify-between p-4">
                                <span class="text-sm text-zinc-900 dark:text-white">{{ $pm->payment_method ? \App\Enums\PaymentMethod::tryFrom($pm->payment_method)?->label() ?? ucfirst($pm->payment_method) : '-' }}</span>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Rp {{ number_format($pm->total_revenue, 0, ',', '.') }}</p>
                                    <p class="text-xs text-zinc-400">{{ $pm->total_orders }} pesanan</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-sm text-zinc-400">Tidak ada data.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Day Type -->
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Tipe Hari</h3>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($dayTypeBreakdown as $dt)
                            <div class="flex items-center justify-between p-4">
                                <span class="text-sm text-zinc-900 dark:text-white">{{ $dt->day_type ? \App\Enums\DayType::tryFrom($dt->day_type)?->label() ?? ucfirst($dt->day_type) : '-' }}</span>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Rp {{ number_format($dt->total_revenue, 0, ',', '.') }}</p>
                                    <p class="text-xs text-zinc-400">{{ $dt->total_orders }} pesanan</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-sm text-zinc-400">Tidak ada data.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

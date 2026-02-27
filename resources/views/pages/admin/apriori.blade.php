<?php

use App\Jobs\RunAprioriJob;
use App\Models\AprioriRun;
use App\Models\AssociationRule;
use App\Services\AprioriService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Analisis Apriori')] #[Layout('layouts.app')] class extends Component {
    public float $minSupport = 0.1;

    public float $minConfidence = 0.5;

    public string $startDate = '';

    public string $endDate = '';

    public bool $isRunning = false;

    public function runAnalysis(): void
    {
        $this->validate([
            'minSupport' => 'required|numeric|min:0.01|max:1',
            'minConfidence' => 'required|numeric|min:0.01|max:1',
        ]);

        RunAprioriJob::dispatch(
            minSupport: $this->minSupport,
            minConfidence: $this->minConfidence,
            startDate: $this->startDate ?: null,
            endDate: $this->endDate ?: null,
            triggeredBy: auth()->id(),
        );

        $this->isRunning = true;
        session()->flash('success', 'Analisis Apriori sedang diproses. Hasil akan muncul setelah selesai.');
    }

    public function runSync(): void
    {
        $this->validate([
            'minSupport' => 'required|numeric|min:0.01|max:1',
            'minConfidence' => 'required|numeric|min:0.01|max:1',
        ]);

        $service = app(AprioriService::class);

        $startDate = $this->startDate ? \Carbon\CarbonImmutable::parse($this->startDate) : null;
        $endDate = $this->endDate ? \Carbon\CarbonImmutable::parse($this->endDate) : null;

        $service->run(
            minSupport: $this->minSupport,
            minConfidence: $this->minConfidence,
            startDate: $startDate,
            endDate: $endDate,
            triggeredBy: auth()->id(),
        );

        session()->flash('success', 'Analisis Apriori selesai!');
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $latestRun = AprioriRun::completed()->latest()->first();

        $view->with([
            'latestRun' => $latestRun,
            'rules' => $latestRun
                ? AssociationRule::where('apriori_run_id', $latestRun->id)
                    ->orderByDesc('lift')
                    ->take(20)
                    ->get()
                : collect(),
            'recentRuns' => AprioriRun::with('triggeredBy')->latest()->take(5)->get(),
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Analisis Apriori</flux:heading>
        <p class="text-zinc-600 dark:text-zinc-400">
            Analisis pola pembelian menggunakan algoritma Apriori untuk menemukan asosiasi antar produk.
        </p>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <!-- Parameters -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">Parameter Analisis</h3>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <flux:input wire:model="minSupport" label="Minimum Support" type="number" step="0.01" min="0.01" max="1" />
                <flux:input wire:model="minConfidence" label="Minimum Confidence" type="number" step="0.01" min="0.01" max="1" />
                <flux:input wire:model="startDate" label="Tanggal Mulai" type="date" />
                <flux:input wire:model="endDate" label="Tanggal Akhir" type="date" />
            </div>
            <div class="mt-4 flex gap-3">
                <flux:button wire:click="runSync" variant="primary" icon="play">
                    Jalankan Sekarang
                </flux:button>
                <flux:button wire:click="runAnalysis" variant="ghost" icon="clock">
                    Jalankan di Background
                </flux:button>
            </div>
        </div>

        <!-- Latest Results -->
        @if($latestRun)
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">Hasil Analisis Terakhir</h3>
                <div class="mb-4 grid gap-4 md:grid-cols-4">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Transaksi</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $latestRun->total_transactions }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Frequent Itemsets</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $latestRun->total_frequent_itemsets }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Association Rules</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $latestRun->total_rules }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Waktu Selesai</p>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $latestRun->completed_at?->diffForHumans() }}</p>
                    </div>
                </div>
                <p class="text-xs text-zinc-400">
                    Support: {{ $latestRun->min_support }} · Confidence: {{ $latestRun->min_confidence }}
                    @if($latestRun->date_from) · Periode: {{ $latestRun->date_from->format('d/m/Y') }} - {{ $latestRun->date_to?->format('d/m/Y') }} @endif
                </p>
            </div>

            <!-- Association Rules -->
            @if($rules->isNotEmpty())
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Top Association Rules (by Lift)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Jika Membeli</th>
                                    <th class="px-4 py-3 text-center font-medium text-zinc-600 dark:text-zinc-400">→</th>
                                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Maka Membeli</th>
                                    <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Support</th>
                                    <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Confidence</th>
                                    <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Lift</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($rules as $rule)
                                    <tr>
                                        <td class="px-4 py-3 text-zinc-900 dark:text-white">
                                            {{ implode(', ', $rule->antecedent) }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-zinc-400">→</td>
                                        <td class="px-4 py-3 text-zinc-900 dark:text-white">
                                            {{ implode(', ', $rule->consequent) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($rule->support * 100, 1) }}%
                                        </td>
                                        <td class="px-4 py-3 text-right text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($rule->confidence * 100, 1) }}%
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold {{ $rule->lift > 1 ? 'text-green-600' : 'text-zinc-600' }}">
                                            {{ number_format($rule->lift, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <flux:icon name="cpu-chip" class="mx-auto size-12 text-zinc-400" />
                <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Belum Ada Analisis</h3>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">Jalankan analisis Apriori untuk melihat pola pembelian.</p>
            </div>
        @endif

        <!-- Recent Runs -->
        @if($recentRuns->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Riwayat Analisis</h3>
                </div>
                @foreach($recentRuns as $run)
                    <div class="flex items-center justify-between border-b border-zinc-100 p-4 last:border-b-0 dark:border-zinc-800">
                        <div>
                            <p class="text-sm text-zinc-900 dark:text-white">
                                Support: {{ $run->min_support }} · Confidence: {{ $run->min_confidence }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $run->total_transactions }} transaksi · {{ $run->total_rules }} rules
                                @if($run->triggeredBy) · {{ $run->triggeredBy->name }} @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <flux:badge size="sm" :color="$run->status === 'completed' ? 'green' : ($run->status === 'running' ? 'yellow' : 'red')">
                                {{ ucfirst($run->status) }}
                            </flux:badge>
                            <p class="mt-1 text-xs text-zinc-400">{{ $run->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

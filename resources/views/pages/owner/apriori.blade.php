<?php

use App\Models\AprioriRun;
use App\Models\AssociationRule;
use App\Models\FrequentItemset;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Analisis Apriori - Pemilik')] #[Layout('layouts.app')] class extends Component {
    public ?int $selectedRunId = null;

    public function rendering(\Illuminate\View\View $view): void
    {
        $runs = AprioriRun::completed()->latest()->take(10)->get();

        $selectedRun = $this->selectedRunId
            ? AprioriRun::find($this->selectedRunId)
            : $runs->first();

        $rules = $selectedRun
            ? AssociationRule::where('apriori_run_id', $selectedRun->id)->orderByDesc('lift')->get()
            : collect();

        $itemsets = $selectedRun
            ? FrequentItemset::where('apriori_run_id', $selectedRun->id)->orderByDesc('support')->take(20)->get()
            : collect();

        // Get top business recommendations (high-lift rules)
        $recommendations = $selectedRun
            ? AssociationRule::where('apriori_run_id', $selectedRun->id)
                ->where('lift', '>', 1.0)
                ->orderByDesc('lift')
                ->take(5)
                ->get()
            : collect();

        $view->with([
            'runs' => $runs,
            'selectedRun' => $selectedRun,
            'rules' => $rules,
            'itemsets' => $itemsets,
            'recommendations' => $recommendations,
        ]);
    }
}; ?>

<div>
    <div class="space-y-6">
        <flux:heading size="xl">Analisis Pola Pembelian</flux:heading>
        <p class="text-zinc-600 dark:text-zinc-400">
            Hasil analisis algoritma Apriori untuk mengetahui pola asosiasi antar produk yang sering dibeli bersamaan.
        </p>

        <!-- Run Selector -->
        @if($runs->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:select wire:model.live="selectedRunId" label="Pilih Hasil Analisis">
                    @foreach($runs as $run)
                        <option value="{{ $run->id }}">
                            {{ $run->completed_at?->format('d/m/Y H:i') }} — Support: {{ $run->min_support }}, Confidence: {{ $run->min_confidence }} ({{ $run->total_transactions }} transaksi)
                        </option>
                    @endforeach
                </flux:select>
            </div>
        @endif

        @if($selectedRun)
            <!-- Summary -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Transaksi</p>
                    <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $selectedRun->total_transactions }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Frequent Itemsets</p>
                    <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $selectedRun->total_frequent_itemsets }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Association Rules</p>
                    <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $selectedRun->total_rules }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Min Support / Confidence</p>
                    <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $selectedRun->min_support }} / {{ $selectedRun->min_confidence }}</p>
                </div>
            </div>

            <!-- Recommendations -->
            @if($recommendations->isNotEmpty())
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-900/20">
                    <h3 class="mb-3 font-semibold text-blue-900 dark:text-blue-300">💡 Rekomendasi Bisnis</h3>
                    <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                        @foreach($recommendations as $rec)
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-blue-500">•</span>
                                <span>
                                    Pelanggan yang membeli <strong>{{ implode(', ', $rec['antecedent']) }}</strong>
                                    memiliki kemungkinan <strong>{{ number_format($rec['confidence'] * 100, 0) }}%</strong>
                                    untuk membeli <strong>{{ implode(', ', $rec['consequent']) }}</strong>
                                    (lift: {{ number_format($rec['lift'], 2) }})
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Frequent Itemsets -->
            @if($itemsets->isNotEmpty())
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Frequent Itemsets</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Kombinasi produk yang sering muncul bersamaan.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Itemset</th>
                                    <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Support</th>
                                    <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Jumlah Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($itemsets as $itemset)
                                    <tr>
                                        <td class="px-4 py-3 text-zinc-900 dark:text-white">
                                            @foreach($itemset->items as $item)
                                                <flux:badge size="sm" class="mr-1">{{ $item }}</flux:badge>
                                            @endforeach
                                        </td>
                                        <td class="px-4 py-3 text-right text-zinc-600 dark:text-zinc-400">{{ number_format($itemset->support * 100, 1) }}%</td>
                                        <td class="px-4 py-3 text-right text-zinc-600 dark:text-zinc-400">{{ $itemset->item_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Association Rules -->
            @if($rules->isNotEmpty())
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Association Rules</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Aturan asosiasi "jika membeli X, maka membeli Y".</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Jika Membeli (Antecedent)</th>
                                    <th class="px-4 py-3 text-center font-medium text-zinc-600 dark:text-zinc-400">→</th>
                                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-400">Maka Membeli (Consequent)</th>
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
                                        <td class="px-4 py-3 text-right font-semibold {{ $rule->lift > 1 ? 'text-green-600' : ($rule->lift < 1 ? 'text-red-500' : 'text-zinc-600') }}">
                                            {{ number_format($rule->lift, 2) }}
                                            @if($rule->lift > 1) ↑ @elseif($rule->lift < 1) ↓ @endif
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
                <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">Belum Ada Hasil Analisis</h3>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">Minta admin untuk menjalankan analisis Apriori terlebih dahulu.</p>
            </div>
        @endif
    </div>
</div>

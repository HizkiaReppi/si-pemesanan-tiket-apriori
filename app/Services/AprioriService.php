<?php

namespace App\Services;

use App\Models\AprioriRun;
use App\Models\AssociationRule;
use App\Models\FrequentItemset;
use App\Models\Order;
use Illuminate\Support\Collection;

class AprioriService
{
    /**
     * Extract transactions from completed orders within a date range.
     *
     * @return array<int, array<string>> Array of transactions, each is array of item keys
     */
    public function extractTransactions(string $dateFrom, string $dateTo): array
    {
        $orders = Order::query()
            ->paid()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('items.itemable')
            ->get();

        $transactions = [];

        foreach ($orders as $order) {
            $items = [];
            foreach ($order->items as $item) {
                $items[] = $item->getAprioriItemKey();
            }

            // Only include orders with 2+ distinct items
            $items = array_unique($items);
            if (count($items) >= 2) {
                $transactions[] = array_values($items);
            }
        }

        return $transactions;
    }

    /**
     * Run the Apriori algorithm on a set of transactions.
     *
     * @param  array<int, array<string>>  $transactions
     * @return array{frequent_itemsets: array<array{items: array<string>, support: float, count: int}>, rules: array<array{antecedent: array<string>, consequent: array<string>, support: float, confidence: float, lift: float}>}
     */
    public function run(array $transactions, float $minSupport = 0.1, float $minConfidence = 0.5): array
    {
        $totalTransactions = count($transactions);

        if ($totalTransactions === 0) {
            return ['frequent_itemsets' => [], 'rules' => []];
        }

        // Step 1: Find frequent 1-itemsets
        $itemCounts = $this->countItems($transactions);
        $frequentItemsets = [];

        $L1 = [];
        foreach ($itemCounts as $item => $count) {
            $support = $count / $totalTransactions;
            if ($support >= $minSupport) {
                $L1[] = [$item];
                $frequentItemsets[] = [
                    'items' => [$item],
                    'support' => round($support, 4),
                    'count' => $count,
                ];
            }
        }

        // Step 2: Generate candidates and find frequent k-itemsets
        $previousL = $L1;
        $k = 2;

        while (! empty($previousL)) {
            $candidates = $this->generateCandidates($previousL, $k);
            $candidateCounts = $this->countCandidates($transactions, $candidates);

            $currentL = [];
            foreach ($candidateCounts as $key => $count) {
                $support = $count / $totalTransactions;
                if ($support >= $minSupport) {
                    $items = explode(',', $key);
                    $currentL[] = $items;
                    $frequentItemsets[] = [
                        'items' => $items,
                        'support' => round($support, 4),
                        'count' => $count,
                    ];
                }
            }

            $previousL = $currentL;
            $k++;
        }

        // Step 3: Generate association rules
        $rules = $this->generateRules($frequentItemsets, $totalTransactions, $minConfidence);

        return [
            'frequent_itemsets' => $frequentItemsets,
            'rules' => $rules,
        ];
    }

    /**
     * Count occurrences of individual items across all transactions.
     *
     * @param  array<int, array<string>>  $transactions
     * @return array<string, int>
     */
    private function countItems(array $transactions): array
    {
        $counts = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction as $item) {
                $counts[$item] = ($counts[$item] ?? 0) + 1;
            }
        }

        arsort($counts);

        return $counts;
    }

    /**
     * Generate candidate k-itemsets from frequent (k-1)-itemsets.
     *
     * @param  array<int, array<string>>  $previousL
     * @return array<int, array<string>>
     */
    private function generateCandidates(array $previousL, int $k): array
    {
        $candidates = [];
        $count = count($previousL);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $itemset1 = $previousL[$i];
                $itemset2 = $previousL[$j];

                sort($itemset1);
                sort($itemset2);

                // Check if first k-2 elements are equal
                $canJoin = true;
                for ($m = 0; $m < $k - 2; $m++) {
                    if (! isset($itemset1[$m], $itemset2[$m]) || $itemset1[$m] !== $itemset2[$m]) {
                        $canJoin = false;
                        break;
                    }
                }

                if ($canJoin) {
                    $candidate = array_unique(array_merge($itemset1, $itemset2));
                    sort($candidate);

                    if (count($candidate) === $k) {
                        $key = implode(',', $candidate);
                        if (! isset($candidates[$key])) {
                            $candidates[$key] = $candidate;
                        }
                    }
                }
            }
        }

        return array_values($candidates);
    }

    /**
     * Count support for candidate itemsets across transactions.
     *
     * @param  array<int, array<string>>  $transactions
     * @param  array<int, array<string>>  $candidates
     * @return array<string, int>
     */
    private function countCandidates(array $transactions, array $candidates): array
    {
        $counts = [];

        foreach ($candidates as $candidate) {
            $key = implode(',', $candidate);
            $counts[$key] = 0;

            foreach ($transactions as $transaction) {
                if (empty(array_diff($candidate, $transaction))) {
                    $counts[$key]++;
                }
            }
        }

        return $counts;
    }

    /**
     * Generate association rules from frequent itemsets.
     *
     * @param  array<array{items: array<string>, support: float, count: int}>  $frequentItemsets
     * @return array<array{antecedent: array<string>, consequent: array<string>, support: float, confidence: float, lift: float}>
     */
    private function generateRules(array $frequentItemsets, int $totalTransactions, float $minConfidence): array
    {
        $rules = [];

        // Build a support lookup
        $supportLookup = [];
        foreach ($frequentItemsets as $itemset) {
            $key = implode(',', $itemset['items']);
            $supportLookup[$key] = $itemset['support'];
        }

        // Generate rules from itemsets with 2+ items
        foreach ($frequentItemsets as $itemset) {
            if (count($itemset['items']) < 2) {
                continue;
            }

            $subsets = $this->getProperSubsets($itemset['items']);

            foreach ($subsets as $antecedent) {
                $consequent = array_values(array_diff($itemset['items'], $antecedent));

                if (empty($consequent)) {
                    continue;
                }

                sort($antecedent);
                sort($consequent);

                $antecedentKey = implode(',', $antecedent);
                $consequentKey = implode(',', $consequent);

                $supportAntecedent = $supportLookup[$antecedentKey] ?? null;
                $supportConsequent = $supportLookup[$consequentKey] ?? null;

                if ($supportAntecedent === null || $supportAntecedent == 0 || $supportConsequent === null || $supportConsequent == 0) {
                    continue;
                }

                $confidence = round($itemset['support'] / $supportAntecedent, 4);
                $lift = round($confidence / $supportConsequent, 4);

                if ($confidence >= $minConfidence) {
                    $rules[] = [
                        'antecedent' => $antecedent,
                        'consequent' => $consequent,
                        'support' => $itemset['support'],
                        'confidence' => $confidence,
                        'lift' => $lift,
                    ];
                }
            }
        }

        // Sort by lift desc
        usort($rules, fn ($a, $b) => $b['lift'] <=> $a['lift']);

        return $rules;
    }

    /**
     * Get all non-empty proper subsets of an itemset.
     *
     * @param  array<string>  $items
     * @return array<array<string>>
     */
    private function getProperSubsets(array $items): array
    {
        $subsets = [];
        $n = count($items);
        $total = pow(2, $n);

        for ($i = 1; $i < $total - 1; $i++) {
            $subset = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i & (1 << $j)) {
                    $subset[] = $items[$j];
                }
            }
            $subsets[] = $subset;
        }

        return $subsets;
    }

    /**
     * Store the results of an Apriori run.
     */
    public function storeResults(AprioriRun $run, array $results): void
    {
        // Store frequent itemsets
        foreach ($results['frequent_itemsets'] as $itemset) {
            FrequentItemset::create([
                'apriori_run_id' => $run->id,
                'items' => $itemset['items'],
                'item_count' => count($itemset['items']),
                'support' => $itemset['support'],
                'transaction_count' => $itemset['count'],
            ]);
        }

        // Store association rules
        foreach ($results['rules'] as $rule) {
            AssociationRule::create([
                'apriori_run_id' => $run->id,
                'antecedent' => $rule['antecedent'],
                'consequent' => $rule['consequent'],
                'support' => $rule['support'],
                'confidence' => $rule['confidence'],
                'lift' => $rule['lift'],
            ]);
        }
    }

    /**
     * Get recommendations based on current cart items.
     *
     * @param  array<string>  $currentItems  Array of item keys in the cart
     * @return Collection<int, AssociationRule>
     */
    public function getRecommendations(array $currentItems, int $limit = 3): Collection
    {
        $latestRun = AprioriRun::query()
            ->latestCompleted()
            ->first();

        if (! $latestRun) {
            return collect();
        }

        return AssociationRule::where('apriori_run_id', $latestRun->id)
            ->where('lift', '>', 1.0)
            ->orderByDesc('lift')
            ->get()
            ->filter(function (AssociationRule $rule) use ($currentItems) {
                // Check if all antecedent items are in the current cart
                return empty(array_diff($rule->antecedent, $currentItems));
            })
            ->take($limit);
    }
}

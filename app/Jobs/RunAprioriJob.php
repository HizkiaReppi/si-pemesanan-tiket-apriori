<?php

namespace App\Jobs;

use App\Events\AprioriCompleted;
use App\Services\AprioriService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunAprioriJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public float $minSupport = 0.1,
        public float $minConfidence = 0.5,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?int $triggeredBy = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AprioriService $aprioriService): void
    {
        Log::info('Apriori job started', [
            'min_support' => $this->minSupport,
            'min_confidence' => $this->minConfidence,
            'date_range' => [$this->startDate, $this->endDate],
        ]);

        $startDate = $this->startDate ? CarbonImmutable::parse($this->startDate) : null;
        $endDate = $this->endDate ? CarbonImmutable::parse($this->endDate) : null;

        $run = $aprioriService->runAnalysis(
            minSupport: $this->minSupport,
            minConfidence: $this->minConfidence,
            startDate: $startDate,
            endDate: $endDate,
            triggeredBy: $this->triggeredBy,
        );

        AprioriCompleted::dispatch($run);

        Log::info('Apriori job completed', [
            'run_id' => $run->id,
            'total_transactions' => $run->total_transactions,
            'total_itemsets' => $run->total_frequent_itemsets,
            'total_rules' => $run->total_rules,
        ]);
    }
}

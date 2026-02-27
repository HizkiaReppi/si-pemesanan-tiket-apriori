<?php

namespace App\Console\Commands;

use App\Jobs\RunAprioriJob;
use Illuminate\Console\Command;

class RunAprioriCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apriori:run
        {--support=0.1 : Minimum support threshold (0-1)}
        {--confidence=0.5 : Minimum confidence threshold (0-1)}
        {--start= : Start date (Y-m-d)}
        {--end= : End date (Y-m-d)}
        {--sync : Run synchronously instead of queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Apriori algorithm to analyze purchase patterns';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minSupport = (float) $this->option('support');
        $minConfidence = (float) $this->option('confidence');
        $startDate = $this->option('start');
        $endDate = $this->option('end');

        if ($minSupport <= 0 || $minSupport > 1) {
            $this->error('Support must be between 0 and 1.');

            return self::FAILURE;
        }

        if ($minConfidence <= 0 || $minConfidence > 1) {
            $this->error('Confidence must be between 0 and 1.');

            return self::FAILURE;
        }

        $this->info('Running Apriori algorithm...');
        $this->table(
            ['Parameter', 'Value'],
            [
                ['Min Support', $minSupport],
                ['Min Confidence', $minConfidence],
                ['Start Date', $startDate ?: 'All'],
                ['End Date', $endDate ?: 'All'],
            ]
        );

        if ($this->option('sync')) {
            $job = new RunAprioriJob(
                minSupport: $minSupport,
                minConfidence: $minConfidence,
                startDate: $startDate,
                endDate: $endDate,
            );

            $job->handle(app(\App\Services\AprioriService::class));

            $this->info('Apriori analysis completed synchronously.');
        } else {
            RunAprioriJob::dispatch(
                minSupport: $minSupport,
                minConfidence: $minConfidence,
                startDate: $startDate,
                endDate: $endDate,
            );

            $this->info('Apriori job dispatched to queue.');
        }

        return self::SUCCESS;
    }
}

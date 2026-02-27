<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrequentItemset extends Model
{
    protected $fillable = [
        'apriori_run_id',
        'items',
        'item_count',
        'support',
        'transaction_count',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'item_count' => 'integer',
            'support' => 'float',
            'transaction_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<AprioriRun, $this>
     */
    public function aprioriRun(): BelongsTo
    {
        return $this->belongsTo(AprioriRun::class);
    }
}

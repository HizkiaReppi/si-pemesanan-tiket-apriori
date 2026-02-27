<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationRule extends Model
{
    protected $fillable = [
        'apriori_run_id',
        'antecedent',
        'consequent',
        'support',
        'confidence',
        'lift',
        'conviction',
    ];

    protected function casts(): array
    {
        return [
            'antecedent' => 'array',
            'consequent' => 'array',
            'support' => 'float',
            'confidence' => 'float',
            'lift' => 'float',
            'conviction' => 'float',
        ];
    }

    /**
     * @return BelongsTo<AprioriRun, $this>
     */
    public function aprioriRun(): BelongsTo
    {
        return $this->belongsTo(AprioriRun::class);
    }

    public function scopeHighLift($query, float $minLift = 1.0)
    {
        return $query->where('lift', '>=', $minLift);
    }

    public function scopeHighConfidence($query, float $minConfidence = 0.5)
    {
        return $query->where('confidence', '>=', $minConfidence);
    }
}

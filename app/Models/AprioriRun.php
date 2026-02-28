<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AprioriRun extends Model
{
    /** @use HasFactory<\Database\Factories\AprioriRunFactory> */
    use HasFactory;

    protected $fillable = [
        'min_support',
        'min_confidence',
        'date_from',
        'date_to',
        'total_transactions',
        'total_items',
        'total_frequent_itemsets',
        'total_rules',
        'status',
        'started_at',
        'completed_at',
        'error_message',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'min_support' => 'float',
            'min_confidence' => 'float',
            'date_from' => 'date',
            'date_to' => 'date',
            'total_transactions' => 'integer',
            'total_items' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * @return HasMany<FrequentItemset, $this>
     */
    public function frequentItemsets(): HasMany
    {
        return $this->hasMany(FrequentItemset::class);
    }

    /**
     * @return HasMany<AssociationRule, $this>
     */
    public function associationRules(): HasMany
    {
        return $this->hasMany(AssociationRule::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeLatestCompleted($query)
    {
        return $query->completed()->latest('completed_at');
    }
}

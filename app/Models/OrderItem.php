<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'itemable_type',
        'itemable_id',
        'item_name',
        'item_price',
        'quantity',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'item_price' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the Apriori item identifier for this order item.
     */
    public function getAprioriItemKey(): string
    {
        if ($this->itemable_type === TicketType::class) {
            return 'ticket:'.($this->itemable?->slug ?? 'unknown');
        }

        return 'addon:'.($this->itemable?->slug ?? 'unknown');
    }
}

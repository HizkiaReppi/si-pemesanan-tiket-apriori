<?php

namespace App\Models;

use App\Enums\DayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPrice extends Model
{
    /** @use HasFactory<\Database\Factories\TicketPriceFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_type_id',
        'day_type',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'day_type' => DayType::class,
            'price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<TicketType, $this>
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }
}

<?php

namespace App\Models;

use App\Enums\DayType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'visit_date',
        'day_type',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'discount',
        'total',
        'status',
        'payment_method',
        'notes',
        'qr_token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'day_type' => DayType::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (empty($order->code)) {
                $order->code = self::generateCode($order->visit_date);
            }
        });
    }

    public static function generateCode(string $visitDate): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        return "ORD-{$date}-{$random}";
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * @return HasMany<CheckIn, $this>
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * @return HasMany<VoucherUsage, $this>
     */
    public function voucherUsages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'auditable_id')
            ->where('auditable_type', self::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [
            OrderStatus::Paid,
            OrderStatus::Confirmed,
            OrderStatus::Completed,
        ]);
    }

    public function isRedeemable(): bool
    {
        return $this->isPaid()
            && $this->visit_date->isToday()
            && $this->checkIns()->where('status', 'valid')->doesntExist();
    }

    public function isReschedulable(): bool
    {
        return $this->isPaid()
            && $this->visit_date->isFuture()
            && $this->visit_date->diffInHours(now()) >= 24;
    }

    public function scopePaid($query)
    {
        return $query->whereIn('status', [
            OrderStatus::Paid,
            OrderStatus::Confirmed,
            OrderStatus::Completed,
        ]);
    }

    public function scopeForStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('visit_date', $date);
    }
}

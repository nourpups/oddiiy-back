<?php

namespace App\Models;

use App\Enum\DeliveryType;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'telegram_message_id',
        'cashback_wallet_option_id',
        'recipient_name',
        'delivery',
        'payment',
        'sum',
        'comment',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'delivery' => DeliveryType::class,
            'payment' => PaymentType::class,
            'status' => OrderStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            OrderItem::class,
            'order_id',
            'id'
        );
    }

    public function cashbackWalletOption(): BelongsTo
    {
        return $this->belongsTo(CashbackWalletOption::class);
    }
}

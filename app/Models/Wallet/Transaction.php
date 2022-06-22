<?php

namespace App\Models\Wallet;

use App\Traits\Models\HasUuid;
use App\Services\Math\MathService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory,HasUuid;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    /**
     * @var string[]
     */
    protected $fillable = [
        'payable_type',
        'payable_id',
        'wallet_id',
        'uuid',
        'type',
        'amount',
        'confirmed',
        'meta',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'wallet_id' => 'int',
        'confirmed' => 'bool',
        'meta' => 'array',
    ];

    public function getTable(): string
    {
        if ((string) $this->table === '') {
            $this->table = config('walletRmx.transaction.table', 'transactions');
        }

        return parent::getTable();
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(config('walletRmx.wallet.model', Wallet::class));
    }

    public function getAmountIntAttribute(): int
    {
        return (int) $this->amount;
    }

    public function getAmountFloatAttribute(): string
    {
        $math = app(MathService::class);
        $decimalPlacesValue = $this->wallet->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        return $math->div($this->amount, $decimalPlaces);
    }
    public function setAmountFloatAttribute(float|int|string $amount): void
    {
        $math = app(MathService::class);
        $decimalPlacesValue = $this->wallet->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        $this->amount = $math->round($math->mul($amount, $decimalPlaces));
    }
}

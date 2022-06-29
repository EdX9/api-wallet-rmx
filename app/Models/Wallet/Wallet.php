<?php

namespace App\Models\Wallet;

use App\Traits\Models\HasUuid;
use App\Traits\Models\Wallet\WalletOperations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory, HasUuid;
    use WalletOperations;
    /**
     * @var string[]
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'name',
        'slug',
        'uuid',
        'description',
        'meta',
        'balance',
        'decimal_places',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'decimal_places' => 'int',
        'meta' => 'json',
    ];

    protected $attributes = [
        'balance' => 0,
        'decimal_places' => 2,
    ];

    public function getTable(): string
    {
        if ((string) $this->table === '') {
            $this->table = config('walletRmx.wallet.table', 'wallets');
        }

        return parent::getTable();
    }

}

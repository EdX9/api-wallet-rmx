<?php

use App\Models\Wallet\Transaction;
use App\Models\Wallet\Transfer;
use App\Models\Wallet\Wallet;


return [
    'math' => [
        'scale' => 64,
    ],
    'lock' => [
        'seconds' => 3,
    ],
    /**
     * Base model 'transaction'.
     */
    'transaction' => [
        'table' => 'transactions',
        'model' => Transaction::class,
    ],

    /**
     * Base model 'transfer'.
     */
    'transfer' => [
        'table' => 'transfers',
        'model' => Transfer::class,
    ],

    /**
     * Base model 'wallet'.
     */
    'wallet' => [
        'table' => 'wallets',
        'model' => Wallet::class,
        'creating' => [],
        'default' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
            'meta' => [],
        ],
    ],
];
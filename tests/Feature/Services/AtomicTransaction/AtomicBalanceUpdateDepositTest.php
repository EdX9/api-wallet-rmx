<?php

use App\Models\User;
use App\Models\Wallet\Wallet;
use App\Services\Math\MathService;
use App\Services\AtomicTransaction\AtomicBalanceUpdate;


beforeEach(function ()
{
    $this->wallet = Wallet::factory()->create([
        'holder_id' => 2,
        'name'=>'Servicios Wallet',
        'slug'=>'servicios-wallet',
    ]);
    $this->math = app(MathService::class);
    $this->atomicTransaction = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);
});


test('Depósitos con saldo variable', function ($saldo,$deposito,$saldoEsperado,$decimales) 
{
    $this->wallet->balance = $saldo;
    $this->wallet->decimal_places = $decimales;
    $this->atomicTransaction = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);
    $this->wallet->save();

    $this->atomicTransaction->deposit($deposito);
    expect($this->wallet->balance)->toBe($saldoEsperado);

})->with([
    ['1500',15,'3000',2],
    ['1500','15','3000',2],
    ['1500',15.00,'3000',2],
    ['1500','15.00','3000',2],
    ['1500','15000000000','1500000001500',2],
    ['1500',15.99,'3099',2],
    ['1500','15.99','3099',2],
    ['1500','15000000000.99','1500000001599',2],
    ['314',3.14,'628',2],
    ['-314',3.14,'0',2],
    ['-314',3.16,'2',2],
    ['1500000',15,'3000000',5],
    [314163,3.14163,'628326',5],
    ['314163','3.14163','628326',5],
    ['314159','3.14159265359','628318',5],
]);


test('Depósitos con metadatos', function ($saldoEsperado,$saldoEntero,$meta) 
{
    $this->atomicTransaction->deposit($saldoEntero,$meta);
    expect($this->wallet->balance)->toBe($saldoEsperado);
    expect($meta)->toBe($this->atomicTransaction->getLastTransaction()[0]->meta);
    
})->with([
    ['1500',15,['testKey'=>'testValue']],
    ['1500',15,['testKey'=>'testValue','multi-nivel'=>['id'=>0]]],
]);

test('Depósitos pendiente', function ($saldoEsperado,$saldoEntero,$meta) 
{

    $saldoOriginal = $this->wallet->balance;

    $this->atomicTransaction->deposit($saldoEntero,$meta,false);

    $transaction = $this->atomicTransaction->getLastTransaction()[0];

    expect($saldoOriginal)->toBe($this->wallet->balance);
    expect($transaction->meta)->toBe($meta);

    $this->atomicTransaction->confirmTransaction($transaction);
    expect($this->wallet->balance)->toBe($saldoEsperado);

})->with([
    ['1500',15,['testKey'=>'testValue']],
    ['1500',15,['testKey'=>'testValue','multi-nivel'=>['id'=>0]]],
]);


/**
 * Errores
 */
test('Depósitos fallido', function ($saldoEntero) 
{
    //dd(1);
    $wallet = $this->wallet;
    $walletBalanceUpdate = $this->atomicTransaction->setWallet($wallet);
    $walletBalanceUpdate->deposit($saldoEntero);

    
})->with([
    '-1500',
    '-1500000000000',
    '-15.99',
    '-0.1599',
    '-15000000000.99'
    -1500,
    -1500000000000,
    -15.99,
    -0.1599,
    -15000000000.99
])->throws(Exception::class,'No debe ingresar números negativos');


test('Wallet no valida', function ($wallet) 
{
    app(AtomicBalanceUpdate::class)->setWallet($wallet);
})
->with([
    null,
    1,
    '',
    'a',
    '1'
])
->throws(TypeError::class);

test('Wallet Modelo no valido', function () 
{
    $wallet = User::factory(1)->create()->first();
    app(AtomicBalanceUpdate::class)->setWallet($wallet);
    
})->throws(Exception::class,'Error Wallet no valida');

test('Wallet no ingresada', function () 
{
    app(AtomicBalanceUpdate::class)->deposit('15.99');
    
})->throws(Exception::class,'Error Wallet no valida');


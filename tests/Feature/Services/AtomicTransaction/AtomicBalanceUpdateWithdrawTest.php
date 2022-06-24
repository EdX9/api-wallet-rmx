<?php

use App\Models\Wallet\Wallet;
use App\Services\Math\MathService;
use App\Services\AtomicTransaction\AtomicBalanceUpdate;

beforeEach(function ()
{
    $this->wallet = Wallet::factory()->create([
        'holder_id' => 2,
        'name'=>'Servicios Wallet',
        'slug'=>'servicios-wallet',
        'balance'=>'50000',
        'decimal_places'=>'2'
    ]);
    $this->math = app(MathService::class);
    $this->atomicTransaction = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);
});

test('Retiros ', function ($saldo,$decimales,$retiro,$saldoEsperado) 
{
    $this->wallet->balance = $saldo;
    $this->wallet->decimal_places = $decimales;
    $this->wallet->save();
    $this->atomicTransaction = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);

    $this->atomicTransaction->withdraw($retiro);
    
    expect($saldoEsperado)->toBe($this->wallet->balance);
    
})->with([
    ['50000' , 2 , 15    ,'48500'],
    ['50000' , 2 ,'15'   ,'48500'],
    ['50000' , 2 , 15.00    ,'48500'],
    ['50000' , 2 ,'15.00'   ,'48500'],
    ['50000' , 2 , 15.99 ,'48401'], 
    ['50000' , 2 ,'15.99','48401'],
    ['50000000' , 5 ,'15.99999','48400001'],
]);

test('Retiros metatdatos ', function ($retiro,$saldoEsperado,$meta) 
{

    $this->atomicTransaction->withdraw($retiro,$meta);

    expect($saldoEsperado)->toBe($this->wallet->balance);
    $transferencia = $this->atomicTransaction->getLastTransaction()[0];
    expect($transferencia->meta)->toBe($meta);
    
})->with([
    [15,'48500',['data'=>'string']],
    ['15','48500',['data'=>'string']],
    [15.99,'48401',['data'=>'string','array'=>['id'=>'5']]],
    ['15.99','48401',['data'=>'string','array'=>['id'=>'5']]],
]);


test('Retiros sin confirmar ', function ($retiro,$saldoEsperado,$meta) 
{
    $saldoOriginal = $this->wallet->balance;
    $this->atomicTransaction->withdraw($retiro,$meta,false);

    expect($saldoOriginal)->toBe($this->wallet->balance);

    $transaccion = $this->atomicTransaction->getLastTransaction()[0];
    expect($transaccion->meta)->toBe($meta);

    $this->atomicTransaction->confirmTransaction($transaccion);
    expect($saldoEsperado)->toBe($this->wallet->balance);
    
})->with([
    [15,'48500',['data'=>'string']],
    ['15','48500',['data'=>'string']],
    [15.99,'48401',['data'=>'string','array'=>['id'=>'5']]],
    ['15.99','48401',['data'=>'string','array'=>['id'=>'5']]],
]);

test('Retiro forzado ', function ($retiro,$balanceEsperado) 
{
    $this->wallet->balance = 0;
    $this->wallet->save();

    $this->atomicTransaction->withdraw($retiro,[],true,true);
    $saldoFinal = $this->wallet->balance;

    expect($balanceEsperado)->toBe($saldoFinal);
    
})->with([
    [15,'-1500'],
    ['15','-1500'],
    [15.99,'-1599'],
    ['15.99','-1599']
]);
/**
 * Errores
 */

test('Retiro fondos insuficientes ', function () 
{   
    $walletBalanceUpdate = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);
    $walletBalanceUpdate->withdraw('500.01');
})
->throws(Exception::class,'Fondos Insuficientes');


test('Retiro fallido', function ($saldoEntero) 
{
    $walletBalanceUpdate = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);
    $walletBalanceUpdate->deposit($saldoEntero);
})
->with([
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
])
->throws(Exception::class,'No debe ingresar números negativos');



test('Wallet no ingresada', function () 
{
    app(AtomicBalanceUpdate::class)->withdraw('15.99');
})
->throws(Exception::class,'Error Wallet no valida');


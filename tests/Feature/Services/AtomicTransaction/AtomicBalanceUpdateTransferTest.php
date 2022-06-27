<?php

use App\Models\Wallet\Transaction;
use App\Models\Wallet\Transfer;
use App\Models\Wallet\Wallet;
use App\Services\Math\MathService;
use App\Services\AtomicTransaction\AtomicBalanceUpdate;

beforeEach(function ()
{
    $this->wallet = Wallet::factory()->create([
        'holder_id' => 1,
        'name'=>'Servicios Wallet',
        'slug'=>'servicios-wallet',
    ]);
    $this->wallet2 = Wallet::factory()->create([
        'holder_id' => 2,
        'name'=>'Servicios Wallet',
        'slug'=>'servicios-wallet',
    ]);
    $this->math = app(MathService::class);
    $this->atomicTransaction = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);
});

test('Transferencia entre wallets', function ($saldoWallet1,$transferencia,$saldoWallet2,$saldoFinalWallet1,$saldoFinalWallet2) 
{
    
    $this->wallet->balance = $saldoWallet1;
    $this->wallet->save();

    $this->wallet2->balance = $saldoWallet2;
    $this->wallet2->save();

    $this->atomicTransaction->transfer($this->wallet2,$transferencia);  
    $transaccion = $this->atomicTransaction->getLastTransaction()[0];
 
    // Saldos Finales
    expect($this->wallet->balance)->toBe($saldoFinalWallet1);
    expect($this->wallet2->balance)->toBe($saldoFinalWallet2);
    // Modelo Proveniente            
    expect( $this->wallet->id)->toBe($transaccion->from_id);
    expect($transaccion->from_type)->toBe('App\Models\Wallet\Wallet');
    // Modelo Receptor
    expect( $this->wallet2->id)->toBe($transaccion->to_id);
    expect($transaccion->to_type)->toBe('App\Models\Wallet\Wallet');
    // Estatus
    expect($transaccion->status)->toBe(Transfer::STATUS_TRANSFER);
    // Deposito             
    $deposito = $transaccion->deposit;
    expect($deposito->id)->toBe($transaccion->deposit_id);
    expect($deposito->payable_type)->toBe( $this->wallet2->holder_type);
    expect($deposito->payable_id)->toBe( $this->wallet2->holder_id);
    expect($deposito->wallet_id)->toBe( $this->wallet2->id);
    expect($deposito->type)->toBe( Transaction::TYPE_DEPOSIT);
    expect($deposito->amount)->toBe( $saldoWallet1);
    expect($deposito->confirmed)->toBeTrue();
    expect($deposito->meta)->toBe([]);
    // Retiro        
    $retiro = $transaccion->withdraw;
    expect($retiro->id)->toBe($transaccion->withdraw_id);
    expect($retiro->payable_type)->toBe( $this->wallet->holder_type);
    expect($retiro->payable_id)->toBe( $this->wallet->holder_id);
    expect($retiro->wallet_id)->toBe( $this->wallet->id);
    expect($retiro->type)->toBe( Transaction::TYPE_WITHDRAW);
    expect($retiro->amount)->toBe( $this->math->negative( $saldoWallet1) );
    expect($retiro->confirmed)->toBeTrue();
    expect($retiro->meta)->toBe([]);

    
})->with([
    [ '50005','500.05', '0' , '0','50005'],
    [ '50000','500.00', '0' , '0','50000'],
    [ '50000','500.00','50000', '0','100000' ],
    [ '50092','500.92','50000', '0','100092' ],
    [ '50092','500.92','50092', '0', '100184'],
    [ '50092','500.92','-50092', '0', '0' ],
    [ '50092','500.92','-50000', '0', '92']
]);

test('Transferencia con monto negativo', function ($saldoWallet1,$saldoWallet2,$montoATransferir) 
{
    $this->wallet->balance = $saldoWallet1;
    $this->wallet->save();

    $this->wallet2->balance= $saldoWallet2;
    $this->wallet2->save();

    $this->atomicTransaction->transfer($this->wallet2,$montoATransferir);   
 
})->with([
    [ -15000,15000,-1],
    [ -15000,15000,-15],
])->throws(Exception::class,'No debe ingresar números negativos');

test('Transferencia entre wallets fondos insuficientes', function ($saldoWallet1,$saldoWallet2,$montoATransferir) 
{
    $this->wallet->balance = $saldoWallet1;
    $this->wallet->save();

    $this->wallet2->balance= $saldoWallet2;
    $this->wallet2->save();

    $this->atomicTransaction->transfer($this->wallet2,$montoATransferir);   
 
})->with([
    [-0.1,10,1],
    [0,10,0.5]
])->throws(Exception::class,'Fondos Insuficientes');

test('No se hagan transferencias entre distintas wallet ',function(){
    $this->wallet3 = Wallet::factory()->create([
        'holder_id' => 3,
        'name'=>'Recargas Wallet',
        'slug'=>'Recargas-wallet',
        'balance'=>'1000'
    ]);
    $this->atomicTransaction = app(AtomicBalanceUpdate::class)->setWallet($this->wallet3);
    $this->atomicTransaction->transfer($this->wallet2,10);   
})
->throws(Exception::class,'Error No se puede Realizar Transacciones entre Diferentes Wallet');

test('Wallet no ingresada', function () 
{
    app(AtomicBalanceUpdate::class)->transfer($this->wallet2,'15');
    
})->throws(Exception::class,'Error Wallet no valida');

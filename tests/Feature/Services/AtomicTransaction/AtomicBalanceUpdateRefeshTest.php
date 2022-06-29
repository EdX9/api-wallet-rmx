<?php

use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transaction;
use App\Services\Math\MathService;
use App\Services\AtomicTransaction\AtomicBalanceUpdate;

beforeEach(function ()
{
    $this->math = app(MathService::class);
    $this->wallet = Wallet::factory()->create([
        'holder_id' => 2,
        'name'=>'Servicios Wallet',
        'slug'=>'servicios-wallet',
    ]);
    $times = 400;
    $deposit = 50000;
    $withdraw = -1;

    $totalDeposit = $this->math->mul($deposit , $times,0);
    $totalWithdraw = $this->math->mul($this->math->abs($withdraw) , $times,0);
    $this->correctBalance = $this->math->sub($totalDeposit,$totalWithdraw,0);

    Transaction::factory()->deposit()->count($times)->create([
        'wallet_id'=>$this->wallet->id,
        'amount' => $deposit
    ]);

    Transaction::factory()->withdraw()->count(400)->create([
        'wallet_id'=>$this->wallet->id,
        'amount' => $withdraw
    ]);
    
    $this->atomicTransaction = app(AtomicBalanceUpdate::class)->setWallet($this->wallet);

});


test('Corregir balance erróneo', function ($saldoWallet1) 
{
    $this->wallet->balance = $saldoWallet1;
    $this->wallet->save();

    $this->atomicTransaction->balanceUpdate();

    expect($this->wallet->balance)->toBe($this->correctBalance);
 
})->with([
    ['15000'],
    ['-15000'],
    ['0'],
]);

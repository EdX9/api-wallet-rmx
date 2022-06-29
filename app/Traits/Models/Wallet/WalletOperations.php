<?php

namespace App\Traits\Models\Wallet;

use App\Models\Wallet\Transaction;
use App\Services\Math\MathService;
use Illuminate\Database\Eloquent\Model;
use App\Services\AtomicTransaction\AtomicBalanceUpdate;

trait WalletOperations
{
    /**
     * Obtiene una nueva instancia de AtomicBalanceUpdate
     * @return AtomicBalanceUpdate
     */
    public function atomicTransaction()
    {
        return app(AtomicBalanceUpdate::class)
        ->setWallet($this);
    }

    /**
     * Agrega la relación entre la wallet y las Transacciones
     */
    public function Transactions()
    {
        return $this->hasMany(config('walletRmx.transaction.model', Transaction::class), 'wallet_id');
    }

    /**
     * Obtiene todas las transacciones de la wallet
     */
    public function getAllTransactions($valid=true)
    {
        return $this->Transactions()->where('confirmed', $valid);
    }

    /**
     * Permite depositar Dinero a la wallet mediante AtomicBalanceUpdate
     */
    public function deposit(string $amount, array $meta = [])
    {
        try {
            $atomic = $this->atomicTransaction();
            $meta = ['deposito'=>''];
            $atomic->deposit($amount,$meta,true);
            $transaction = $atomic->getLastTransaction()[0];
        } catch (\Throwable $th) {
            $transaction = null;
        }
        return $transaction;
    }
    
    /**
     * Permite retirar dinero de la wallet mediante AtomicBalanceUpdate
     */
    public function withdraw(string $amount, array $meta = [])
    {
        try {
            $atomic = $this->atomicTransaction();
            $atomic->withdraw($amount,$meta ,true,true);
            $transaction = $atomic->getLastTransaction()[0];
        } catch (\Throwable $th) {
            $transaction = null;
        }
        return $transaction;
    }

    /**
     * Permite hacer un deposito que se quedara pendiente
     */
    public function pendingDeposit(string $amount, array $meta = [])
    {
        try {
            $atomic = $this->atomicTransaction();
            $atomic->deposit($amount,$meta,false);
            $transaction = $atomic->getLastTransaction()[0];
        } catch (\Throwable $th) {
            $transaction = null;
        }
        return $transaction;
    }
    /**
     * Forzara el retiro aun que no tenga los fondos suficientes 
     */
    public function forceWithdraw(string $amount, array $meta = [])
    {
        try {
            $atomic = $this->atomicTransaction();
            $atomic->withdraw($amount,$meta ,true,true);
            $transaction = $atomic->getLastTransaction()[0];
        } catch (\Throwable $th) {
            $transaction = null;
        }
        return $transaction;
    }

    /**
     *  
     */
    public function transfer(Model $walletReceiver, string $amount, array $meta = [])
    {
        try {
            $atomic = $this->atomicTransaction();
            $atomic->transfer($walletReceiver,$amount,$meta,false);
            $transaction = $atomic->getLastTransaction()[0];
        } catch (\Throwable $th) {
            $transaction = null;
            dd($th);
        }
        return $transaction;
    }
    /**
     *  
     */
    public function transferForce(Model $walletReceiver, string $amount, array $meta = [])
    {
        try {
            $atomic = $this->atomicTransaction();
            $atomic->transfer($walletReceiver,$amount,$meta,true);
            $transaction = $atomic->getLastTransaction()[0];
        } catch (\Throwable $th) {
            $transaction = null;
        }
        return $transaction;
    }
    /**
     * Obtiene el balance con la configuración de decimales establecida 
     */
    public function getBalance()
    {
        $math = app(MathService::class);
        $decimalPointAdjustment = $math->powTen($this->decimal_places);
        return $math->div($this->balance,$decimalPointAdjustment,$this->decimal_places); 
    }

    /**
     * Permite actualizar el balance de una cuenta 
     * Advertencia esta operación bloqueara todas 
     * las transacciones sobre esta wallet
     */
    public function updateBalance()
    {
       try {
            $atomic = $this->atomicTransaction();
            $atomic->setTtlBlock(15);
            $atomic->balanceUpdate();
            $atomic->setTtlBlock(config('walletRmx.lock.seconds',3));
       } catch (\Throwable $th) {
            dd($th);
       }
       return  $this->getBalance();
    }

}

<?php

namespace App\Services\AtomicTransaction;

use Exception;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transaction;
use App\Services\Lock\lockService;
use App\Services\Math\MathService;
use Illuminate\Support\Facades\DB;

class AtomicBalanceUpdate
{
    protected $lastTransaction;
    private $ttlBlock = 3;
    private $decimalPlacesWallet;
    private $decimalPlacesAdjustment;

    public function __construct(
        private lockService $cache,
        private MathService $math,
        private Wallet $wallet
        ) 
    {
        $this->decimalPlacesWallet = $this->wallet->decimal_places;
        $this->decimalPlacesAdjustment = $math->powTen($this->decimalPlacesWallet);
    }

    public function setTtlBlock(int $ttlBlock)
    {
        $this->ttlBlock = $ttlBlock;
    }

    public function deposit(string $amount,array $meta = [],bool $confirmed = true)
    {
        
        return $this->cache->lock('wallet:update:balance:lock',$this->ttlBlock,function() use ($amount,$meta,$confirmed){
            DB::transaction(function () use ($amount,$meta,$confirmed) { // Start the transaction
                $this->wallet->balance = $this->getBalanceToDecimal(
                    $this->math->add(
                        $this->wallet->balance,$amount,
                        $this->wallet->decimal_places));   
                $this->wallet->save();
                $this->setTransaction(Transaction::TYPE_DEPOSIT,$amount,$confirmed,$meta);
                
            }); // End transaction
            
            return $this->wallet;
         });
    }

    public function withdraw(string $amount,array $meta = [],bool $confirmed = true,bool $force=false)
    {
        $amount=$this->getBalanceToDecimal($amount);
        return $this->cache->lock('wallet:update:balance:lock',$this->ttlBlock,function() use ($amount,$meta,$confirmed,$force)
        {
            if(!$force){
                if($this->math->compare($this->wallet->balance,$amount) == -1)
                {
                    throw new Exception('Fondos Insuficientes');
                }
            }
            DB::transaction(function () use ($amount,$meta,$confirmed) { // Start the transaction
                $this->wallet->balance = 
                    $this->math->sub(
                        $this->wallet->balance,
                        $amount,
                        $this->wallet->decimal_places);    
                $this->wallet->save();
                $this->setTransaction(Transaction::TYPE_WITHDRAW,$amount,$confirmed,$meta);
            }); // End transaction
            return $this->wallet;
         });
    }

    private function getBalanceToDecimal(string $balance)
    {
        return $this->math->mul($balance,$this->decimalPlacesAdjustment,0);
    }

    private function setTransaction(string $type, string $amount,bool $confirmed,array $meta)
    {
        $this->lastTransaction = Transaction::create(
            [
                'payable_type' => $this->wallet->holder_type,
                'payable_id' => $this->wallet->holder_id,
                'wallet_id' => $this->wallet->id,
                'type' => $type,
                'amount' => $amount,
                'confirmed' => $confirmed,
                'meta'=>$meta,
            ]
        );
    }

    public function getLastTransaction()
    {
        return [$this->lastTransaction,$this->wallet];
    }
}

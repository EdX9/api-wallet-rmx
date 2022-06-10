<?php

namespace App\Services\AtomicTransaction;

use Exception;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transaction;
use App\Models\Wallet\Transfer;
use App\Services\Lock\lockService;
use App\Services\Math\MathService;
use Illuminate\Support\Facades\DB;
use Illuminate\Config\Repository as ConfigRepository;

class AtomicBalanceUpdate
{
    protected $lastTransaction;
    private $ttlBlock;
    private $decimalPlacesWallet;
    private $decimalPlacesAdjustment;
    private $wallet;
    private $walletClass;

    public function __construct(
        private lockService $cache,
        private MathService $math,
        private ConfigRepository $config
        ) 
    {
        $this->walletClass = $this->config->get('walletRmx.wallet.model',  Wallet::class);
        $this->ttlBlock = $this->config->get('walletRmx.lock.seconds', 3);
    }

    public function setTtlBlock(int $ttlBlock)
    {
        $this->ttlBlock = $ttlBlock;
    }

    private function verifyWallet()
    {
        if (!$this->wallet instanceof $this->walletClass) {
            throw new Exception('Error Wallet no valida');
        }
    }

    public function setWallet($wallet)
    {
        $this->wallet = $wallet;
        $this->verifyWallet();
        $this->decimalPlacesWallet = $this->wallet->decimal_places;
        $this->decimalPlacesAdjustment = $this->math->powTen($this->decimalPlacesWallet);
        return $this;
    }

    public function deposit(string $amount,array $meta = [],bool $confirmed = true)
    {
        $this->verifyWallet();
        $amount=$this->getBalanceToDecimal($amount);
        return $this->cache->lock('wallet:update:balance:lock',$this->ttlBlock,function() use ($amount,$meta,$confirmed){
            // Start the transaction
            DB::transaction(function () use ($amount,$meta,$confirmed) { 
                $this->wallet->balance = $this->math->add(
                        $this->wallet->balance,$amount,
                        $this->wallet->decimal_places);
                $this->wallet->save();
                $this->setTransaction(Transaction::TYPE_DEPOSIT,$amount,$confirmed,$meta);
                
            }); 
            // End transaction
            return $this->wallet;
         });
    }

    public function withdraw(string $amount,array $meta = [],bool $confirmed = true,bool $force=false)
    {
        $this->verifyWallet();
        $amount=$this->getBalanceToDecimal($amount);
        return $this->cache->lock('wallet:update:balance:lock',$this->ttlBlock,function() use ($amount,$meta,$confirmed,$force)
        {
            if(!$force){
                if($this->math->compare($this->wallet->balance,$amount) == -1)
                {
                    throw new Exception('Fondos Insuficientes');
                }
            }
            // Start the transaction
            DB::transaction(function () use ($amount,$meta,$confirmed) { 
                $this->wallet->balance = 
                    $this->math->sub(
                        $this->wallet->balance,
                        $amount,
                        $this->wallet->decimal_places);    
                $this->wallet->save();
                $amount = $this->math->negative($amount);
                $this->setTransaction(Transaction::TYPE_WITHDRAW,$amount,$confirmed,$meta);
            }); 
            // End transaction
            return $this->wallet;
         });
    }

    public function transfer($walletReceiver,string $amount,array $meta = [],bool $force=false)
    {
        // Start the transaction
        DB::transaction(function () use ($amount,$walletReceiver,$meta,$force) { 
            $originalWallet = $this->wallet;
            $this->withdraw($amount,$meta,true,$force);
            $withdraws = $this->getLastTransaction()[0];

            $this->setWallet($walletReceiver);

            $this->deposit($amount,$meta,true);
            $deposit = $this->getLastTransaction()[0];
            $this->setWallet($originalWallet);
            $this->setTransfer($originalWallet, $walletReceiver,Transfer::STATUS_TRANSFER,$deposit,$withdraws);

        }); 
        // End transaction
        return $this->wallet;
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

    private function setTransfer($wallet, $walletReceiver,string $type,$deposit,$withdraws)
    {
        
        $this->lastTransaction = Transfer::create(
            [
                'from_type' => $this->walletClass,
                'from_id' => $wallet->id,
                'to_type' => $this->walletClass,
                'to_id' => $walletReceiver->id,
                'status' => $type,
                'deposit_id' => $deposit->id,
                'withdraw_id' => $withdraws->id,
            ]
        );
    }

    public function getLastTransaction()
    {
        return [$this->lastTransaction,$this->wallet];
    }
}

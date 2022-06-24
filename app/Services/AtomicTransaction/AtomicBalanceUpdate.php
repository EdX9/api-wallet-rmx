<?php

namespace App\Services\AtomicTransaction;

use Exception;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transfer;
use App\Models\Wallet\Transaction;
use App\Services\Lock\lockService;
use App\Services\Math\MathService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Config\Repository as ConfigRepository;

/**
 * Clase para realizar operaciones Atómicas utilizando 
 * el bloqueo de registro y transacciones seguras sobre 
 * las wallet evitando colisiones
 */
class AtomicBalanceUpdate
{
    protected $lastTransaction;
    private $ttlBlock;
    private $decimalPlacesWallet;
    private $decimalPlacesAdjustment;
    private $wallet;
    private $walletClass;
    private const BLOCK_TRANSFER_DISTINCT_WALLETS = true;
    private $lockKey;

    public function __construct(
        private lockService $cache,
        private MathService $math,
        private ConfigRepository $config
        ) 
    {
        /**
         * Configura las opciones sobre el archivo config/walletRmx
         */
        // Modelo de Wallet
        $this->walletClass      = $this->config->get('walletRmx.wallet.model',  Wallet::class);
        // Tiempo de Bloqueo
        $this->ttlBlock         = $this->config->get('walletRmx.lock.seconds', 3);
        // Modelo de Transferencia
        $this->classTransfer    = $this->config->get('walletRmx.transfer.model',  Transfer::class);
        // Modelo de Transacción
        $this->classTransaction = $this->config->get('walletRmx.transaction.model', Transaction::class);
    }

    /**
     * Cambia el tiempo de bloqueo 
     */
    public function setTtlBlock(int $ttlBlock):void
    {
        $this->ttlBlock = $ttlBlock;
    }

    /**
     * Verifica si la variable $wallet es una instancia del modelo Configurado
     */
    private function verifyWallet():void
    {
        if (!$this->wallet instanceof $this->walletClass) {
            throw new Exception('Error Wallet no valida');
        }
    }

    /**
     * Verifica si la variable $transaction es una instancia del modelo Configurado
     * y verifica que la transacción no este confirmada
     */
    public function verifyTransaction(Model $transaction):void
    {
        if (!$transaction instanceof $this->classTransaction) {
            throw new Exception('Error Transacción no valida');
        }
        if ($transaction->confirmed) {
            throw new Exception('Error Transacción previamente validada');
        }
    }

    /**
     * Establece la wallet sobre la cual se realizaran las operaciones
     */
    public function setWallet(Model $wallet):self 
    {
        //Asigna la wallet a utilizar 
        $this->wallet = $wallet;
        // verifica que sea valida
        $this->verifyWallet();
        // Genera la llave de bloqueo
        $this->lockKey = 'wallet'.$this->wallet->id.':update:balance:lock';
        // Se obtiene el numero de decimales que tiene configurada la wallet
        $this->decimalPlacesWallet = $this->wallet->decimal_places;
        // Obtiene el valor de ajuste de decimales 
        
        $this->decimalPlacesAdjustment = $this->math->round($this->math->powTen($this->decimalPlacesWallet));
        return $this;
    }

    /**
     * Realiza un deposito a la wallet configurada
     */
    public function deposit(string $amount,array $meta = [],bool $confirmed = true) : Model
    {
        // Verifica que el monto sea Positivo 
        $this->verifyPositiveAmount($amount);
        // Verifica que la wallet sea valida
        $this->verifyWallet();
        // Convierte la cantidad a decimales 
        $amount = $this->castToDecimal($amount);
        // Inicia el Bloqueo de la transacción 
        return $this->cache->lock($this->lockKey,$this->ttlBlock,function() use ($amount,$meta,$confirmed){
            // Se inicia la Transacción en la base de datos
            DB::transaction(function () use ($amount,$meta,$confirmed) {
                // Verifica si es un deposito confirmado 
                if($confirmed){
                    // Realiza la actualización del Balance
                    $this->incrementWalletBalance($amount);
                }
                // Genera el registro de la Transacción realizada
                $this->createTransaction($this->classTransaction::TYPE_DEPOSIT,$amount,$confirmed,$meta);
            }); 
            // Finaliza la transacción de la base de datos
            return $this->wallet;
         });
    }

    /**
     * Realiza un retiro a la wallet configurada
     */
    public function withdraw(string $amount,array $meta = [],bool $confirmed = true,bool $force=false): Model
    {
        
        // Verifica que el monto sea Positivo
        $this->verifyPositiveAmount($amount);
        // Verifica que la wallet sea valida
        $this->verifyWallet();
        // Convierte la cantidad a decimales 
        $amount = $this->castToDecimal($amount);
        // Inicia el Bloqueo de la transacción
        return $this->cache->lock($this->lockKey,$this->ttlBlock,function() use ($amount,$meta,$confirmed,$force)
        {
            // Verifica si el retiro es forzado
            if(!$force){
                // En caso de no serlo verifica los fondos 
                if($this->math->compare($this->wallet->balance,$amount) == -1)
                {
                    throw new Exception('Fondos Insuficientes');
                }
            }
            // Se inicia la Transacción en la base de datos
            DB::transaction(function () use ($amount,$meta,$confirmed) {
                // Verifica si es un retiro confirmado
                if($confirmed){
                    // Realiza la actualización del Balance
                    $this->decrementWalletBalance($amount);
                }
                // Convierte el Monto a Negativo para el registro
                $amount = $this->math->negative($amount);
                // Genera el registro de la transacción
                $this->createTransaction($this->classTransaction::TYPE_WITHDRAW,$amount,$confirmed,$meta);
            }); 
            // Finaliza la transacción de la base de datos
            return $this->wallet;
         });
    }

    /**
     * Realiza una transferencia de la wallet configurada a otra 
     */
    public function transfer($walletReceiver,string $amount,array $meta = [],bool $force=false): Model
    {
        return $this->transaction($amount,$walletReceiver,$meta,$force, $this->classTransfer::STATUS_TRANSFER);
    }

    /**
     * Pendientes hasta desarrollar el modelo de producto
     */

     /**
      * Realiza el Pago de un producto
      */
    //public function pay($walletReceiver,string $amount,array $meta = [],bool $force=false): Model
    //{
    //    return $this->transaction($amount,$walletReceiver,$meta,$force, $this->classTransfer::STATUS_PAID);
    //}

    /**
     * Realiza una devolución 
     */
    //public function refound($walletReceiver,string $amount,array $meta = [],bool $force=false): Model
    //{
    //    return $this->transaction($amount,$walletReceiver,$meta,$force, $this->classTransfer::STATUS_REFUND);
    //}

    /**
     * Genera una transacción entre la wallet configurada y otra
     */
    private function transaction(string $amount,Model $walletReceiver,array $meta = [],$force = false,string $type): Model
    {
        // Verifica que el monto sea Positivo
        $this->verifyPositiveAmount($amount);
        // Se inicia la Transacción en la base de datos
        DB::transaction(function () use ($amount,$walletReceiver,$meta,$force,$type) { 
            // Se guarda la wallet configurada en una variable pivote
            $originalWallet = $this->wallet;
            // Se Realiza el retiro  
            $this->withdraw($amount,$meta,true,$force);
            // Se almacena el resultado de la transacción
            $withdraws = $this->getLastTransaction()[0];
            // Se intercambia la wallet receptora para hacer un deposito
            $this->setWallet($walletReceiver);
            // Verifica que las reglas de transferencia se cumplan 
            $this->checkTransactionRules($type,$originalWallet,$walletReceiver);
            // Se realiza el deposito 
            $this->deposit($amount,$meta,true);
            // Se almacena el resultado de la transacción
            $deposit = $this->getLastTransaction()[0];
            // Se vuelve a asignar la wallet original 
            $this->setWallet($originalWallet);
            // Se crea un registro de Transferencia 
            $this->createTransfer($originalWallet, $walletReceiver,$type,$deposit,$withdraws);

        }); 
        // Finaliza la transacción de la base de datos
        return $this->wallet;
    }

    /**
     * Realiza la confirmación de un movimiento 
     */
    public function confirmTransaction(Model $transaction): Model
    {
        $this->verifyTransaction($transaction);
        return $this->cache->lock($this->lockKey,$this->ttlBlock,function() use ($transaction){
            // Start the transaction
            DB::transaction(function () use ($transaction) { 
                $transaction->confirmed = true;
                $transaction->save();
                $this->incrementWalletBalance($transaction->amount);
            }); 
            // End transaction
            return $this->wallet;
         });
    }

    /**
     * Convierte el monto a un decimal dependiendo de la configuración de la wallet
     */
    private function castToDecimal(string $amount):string
    {
        return $this->math->mul($amount,$this->decimalPlacesAdjustment,0);
    }

    /**
     * Crea una nueva transacción en la base de Datos
     */
    private function createTransaction(string $type, string $amount,bool $confirmed,array $meta):void
    {
        $this->lastTransaction = $this->classTransaction::create(
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

    /**
     * Crea una Transferencia en la base de datos
     */
    private function createTransfer(Model $wallet,Model $walletReceiver,string $type,Model $deposit,Model $withdraws):void
    {
        
        $this->lastTransaction =  $this->classTransfer::create(
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

    /**
     * Obtiene la ultima transacción realizada
     */
    public function getLastTransaction() : array
    {
        return [$this->lastTransaction,$this->wallet];
    }

    /**
     * Revisa las reglas de Transferencias
     */
    private function checkTransactionRules(string $type,Model $originalWallet,Model $walletReceiver):void
    {
        /**Verifica si la transferencia se realiza entre wallets del mismo tipo */
        if(
            self::BLOCK_TRANSFER_DISTINCT_WALLETS && 
            $type== $this->classTransfer::STATUS_TRANSFER && 
            $originalWallet->slug !== $walletReceiver->slug)
        {
            throw new Exception('Error No se puede Realizar Transacciones entre Diferentes Wallet', 1);
        }
    }

    /**
     * Valida que el monto sea positivo
     */
    private function verifyPositiveAmount(string $amount):void
    {
        if($this->math->isNegative($amount)){
            throw new Exception("No debe ingresar números negativos", 1);
        };
    }

    /**
     * Incrementa el balance de la wallet
     */
    private function incrementWalletBalance(string $amount):void
    {
        $this->wallet->balance = $this->math->add(
            $this->wallet->balance,$amount,
            0);
        $this->wallet->save();    
    }

    /**
     * Decremental el balance de la wallet
     */
    private function decrementWalletBalance(string $amount):void
    {
        $this->wallet->balance = $this->math->sub(
            $this->wallet->balance,
            $amount,
            0);
        $this->wallet->save();
    }
}

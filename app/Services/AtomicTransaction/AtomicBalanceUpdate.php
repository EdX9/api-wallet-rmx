<?php

namespace App\Services\AtomicTransaction;

use Exception;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transfer;
use App\Models\Wallet\Transaction;
use App\Services\Math\MathService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Config\Repository as ConfigRepository;
use App\Services\AtomicTransaction\AtomicTransactionDatabaseService;

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
    private $amountMaxRange;


    public function __construct(
        private AtomicTransactionDatabaseService $atomic,
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
        // Rango Máximo
        $this->amountMaxRange = $this->config->get('walletRmx.math.scale', 64) -1;
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
    private function verifyWallet($wallet=null):void
    {
        $wallet = is_null($wallet) ? $this->wallet : $wallet;
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
     * Valida que el monto sea positivo
     */
    private function verifyPositiveAmount(string $amount):void
    {
        if($this->math->isNegative($amount)){
            throw new Exception("No debe ingresar números negativos", 1);
        };
    }
    /**
     * Valida que el monto este dentro del Rango Soportado
     */
    private function verifyAmountRange(string $amount):void
    {
        $amountLen = strlen(str_replace(['-','.'],['',''],$amount));
        if ($amountLen > $this->amountMaxRange ) {
            throw new Exception('Monto fuera de Rango',0);
        }
    }

    /**
     * Realiza las validaciones para depósitos y retiros
     */
    public function validar(string $amount)
    {
        // Verificar el rango
        $this->verifyAmountRange($amount);
        // Verifica que el monto sea Positivo 
        $this->verifyPositiveAmount($amount);
        // Verifica que la wallet sea valida
        $this->verifyWallet();
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
    public function deposit(string $amount,array $meta = [],bool $confirmed = true) 
    {
        // Realiza una verificación
        $this->validar($amount);
        // Convierte la cantidad a decimales 
        $amount = $this->castToDecimal($amount);
        // Inicia el Bloqueo de la transacción 
        return $this->atomic->AtomicTransaction($this->lockKey,$this->ttlBlock,function () use ($amount,$meta,$confirmed) {
            // Verifica si es un deposito confirmado 
            if($confirmed){
                // Realiza la actualización del Balance
                $this->incrementWalletBalance($amount);
            }
            // Genera el registro de la Transacción realizada
            $this->createTransaction($this->classTransaction::TYPE_DEPOSIT,$amount,$confirmed,$meta);
            return $this->wallet;
        });
    }

    /**
     * Realiza un retiro a la wallet configurada
     */
    public function withdraw(string $amount,array $meta = [],bool $confirmed = true,bool $force=false)
    {
        // Realiza una verificación
        $this->validar($amount);
        // Convierte la cantidad a decimales 
        $amount = $this->castToDecimal($amount);
        // Inicia el Bloqueo de la transacción
        return $this->atomic->AtomicTransaction($this->lockKey,$this->ttlBlock,function () use ($amount,$meta,$confirmed,$force)
        {
            // Verifica si el retiro es forzado
            if(!$force){
                // En caso de no serlo verifica los fondos 
                if($this->math->compare($this->wallet->balance,$amount) == -1)
                {
                    throw new Exception('Fondos Insuficientes');
                }
            }
            // Verifica si es un retiro confirmado
            if($confirmed){
                // Realiza la actualización del Balance
                $this->decrementWalletBalance($amount);
            }
            // Convierte el Monto a Negativo para el registro
            $amount = $this->math->negative($amount);
            // Genera el registro de la transacción
            $this->createTransaction($this->classTransaction::TYPE_WITHDRAW,$amount,$confirmed,$meta);
        
            return $this->wallet;
         });
    }

    /**
     * Realiza una transferencia de la wallet configurada a otra 
     */
    public function transfer(Model $walletReceiver, string $amount, array $meta = [],  bool $force = false)
    {
        return $this->transaction(
            $amount,
            $walletReceiver,
            $this->classTransfer::STATUS_TRANSFER,
            $meta,$force);
    }

    /**
     * Pendientes hasta desarrollar el modelo de producto
     */

     /**
      * Realiza el Pago de un producto
      */
    //public function pay($walletReceiver,string $amount,array $meta = [],bool $force=false)
    //{
    //    return $this->transaction($amount,$walletReceiver,$this->classTransfer::STATUS_PAID,$meta,$force);
    //}

    /**
     * Realiza una devolución 
     */
    //public function refound($walletReceiver,string $amount,array $meta = [],bool $force=false)
    //{
    //    return $this->transaction($amount,$walletReceiver,$this->classTransfer::STATUS_REFUND,$meta,$force);
    //}

    /**
     * Genera una transacción entre la wallet configurada y otra
     */
    private function transaction(string $amount, Model $walletReceiver, string $type, array $meta = [], $force = false)
    {
        // Verifica que el monto sea Positivo
        $this->verifyPositiveAmount($amount);
        // Verificar wallet receptora
        $this->verifyWallet($walletReceiver);
        // Verifica que las reglas de transferencia se cumplan 
        $this->checkTransactionRules($type,$this->wallet,$walletReceiver);
        // Se asigna una Key especial para bloquear transacciones de la wallet origen
        $key = 'wallet'.$this->wallet->id.':transaction:balance:lock';
        // Se inicia la Transacción en la base de datos
        return $this->atomic->AtomicTransaction($key,$this->ttlBlock,function () use ($amount,$walletReceiver,$meta,$force,$type) { 
            // Se guarda la wallet configurada en una variable pivote
            $originalWallet = $this->wallet;
            // Se Realiza el retiro  
            $this->withdraw($amount,$meta,true,$force);
            // Se almacena el resultado de la transacción
            $withdraws = $this->getLastTransaction()[0];
            // Se intercambia la wallet receptora para hacer un deposito
            $this->setWallet($walletReceiver);
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
    public function confirmTransaction(Model $transaction)
    {
        $this->verifyTransaction($transaction);
        return $this->atomic->AtomicTransaction($this->lockKey,$this->ttlBlock,function () use ($transaction){
            $transaction->confirmed = true;
            $transaction->save();
            $this->incrementWalletBalance($transaction->amount);
            return $this->wallet;
         });
    }

    /**
     * Actualización de Balance, esta acción bloquea la wallet durante el proceso
     */
    public function balanceUpdate()
    {
        return $this->atomic->AtomicTransaction($this->lockKey,$this->ttlBlock,function ()
        {
            $updateAmount = $this->wallet->getAllTransactions()->sum('amount');
            if ($this->math->compare($updateAmount,$this->wallet->balance) !== 0 ) 
            {
                $this->wallet->balance = $updateAmount;
                $this->wallet->save();
            }
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
    /**
     * Obtiene la ultima transacción realizada
     */
    public function getLastTransaction() : array
    {
        return [$this->lastTransaction,$this->wallet];
    }
}

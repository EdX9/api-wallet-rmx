<?php

namespace App\Services\AtomicTransaction;

use Closure;
use App\Services\Lock\lockService;
use App\Services\Database\TransactionService;
/**
 * Servicio de bloqueo por cache y transacciones seguras
 */
class AtomicTransactionDatabaseService
{
    public function __construct(
        private lockService $cacheLock,
        private TransactionService $dbTransaction
        ){}

    public function AtomicTransaction(string $key,int $ttl,Closure $Closure,int $attempts = 1)
    {
        return $this->cacheLock->lock(
            $key,
            $ttl,
            $this->dbTransaction->transaction($Closure,$attempts)
        );
    }
}

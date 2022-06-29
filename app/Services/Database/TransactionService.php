<?php

namespace App\Services\Database;

use Closure;
use Illuminate\Support\Facades\DB;
/**
 * Servicio de transacciones de base de datos
 */
class TransactionService
{
    public function transaction(Closure $function,int $attempts = 1)
    {
        return DB::transaction($function,$attempts);
    }
}

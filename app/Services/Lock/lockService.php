<?php

namespace App\Services\Lock;

use Illuminate\Support\Facades\Cache;
/**
 * Servicio de Bloqueo por cache
 */
class lockService
{
    public function __construct() {
        $this->Cache = new Cache();
    }

    public function lock($key,$ttl,$Closure)
    {
        return $this->Cache::lock($key, $ttl)->block(5, $Closure);
    }
}

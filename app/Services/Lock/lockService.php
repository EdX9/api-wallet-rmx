<?php

namespace App\Services\Lock;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class lockService
{
    public function __construct() {
        $this->Cache = new Cache();
    }
    /**
     * ELIMINAR
     */
    public function test()
    {
        Redis::set('name', 'Jack');
        Cache::store('redis')->put('addTestR', 'valueR');
        Cache::add('addTest', 'value', 1*60);
    }

    public function lock($key,$ttl,$Closure)
    {
        return $this->Cache::lock($key, $ttl)->block(5, $Closure);
    }
}

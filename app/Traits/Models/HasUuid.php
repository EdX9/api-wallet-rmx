<?php

namespace App\Traits\Models;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        // Si no Contiene UUID lo Genera
        static::creating(function($model){
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }
}

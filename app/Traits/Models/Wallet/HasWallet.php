<?php

namespace App\Traits\Models\Wallet;

use App\Models\Wallet\Wallet;

trait HasWallet
{

    public function getWalletConfig(string $attr)
    {
        return config('walletRmx.'.$attr);
    }

    public function getWallet(string $slug)
    { 
        $consulta = Wallet::where('holder_type','App\Models\User')
        ->where('holder_id',$this->id)
        ->where('slug',$slug);
        return $consulta->first();
    }

    public function createWallet(String $name,String $slug,string $description,Int $decimalPlaces=2,Array $meta=[])
    { 
        if(is_null($this->getWallet($slug)))
        {
            $wallet = $this->getWalletConfig('wallet.model');
            $wallet::create([
                'holder_type'=>'App\Models\User',
                'holder_id'=>$this->id,
                'name'=>$name,
                'slug'=>$slug,
                'description'=>$description,
                'meta'=>$meta,
                'decimal_places'=>$decimalPlaces,
            ]);
        }
            
    }
}

<?php

namespace Database\Factories\Wallet;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet\Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $rand = $this->faker->numberBetween(0, 100);
        $wallet = \App\Models\Wallet\Wallet::create(
            [
                'holder_type' => "App\Models\User",
                'holder_id' => 1,
                'name'=>'WalletTestTransfer'.$rand,
                'slug'=>'Wallet-SlugTransfer'.$rand,
            ]
        );
        $wallet2 = \App\Models\Wallet\Wallet::create(
            [
                'holder_type' => "App\Models\User",
                'holder_id' => 2,
                'name'=>'WalletTestTransfer'.$rand,
                'slug'=>'Wallet-SlugTransfer'.$rand,
            ]
        );
        $amount = $this->faker->numberBetween();
        $tran1 = \App\Models\Wallet\Transaction::create(
            [
                'payable_type'=>"App\Models\User",
                'payable_id'=>2,
                'wallet_id'=>$wallet2->id,
                'type'=>'deposit',
                'amount'=>$amount,
                'confirmed'=>1
            ]
        );
        $tran2 = \App\Models\Wallet\Transaction::create(
            [
                'payable_type'=>"App\Models\User",
                'payable_id'=>1,
                'wallet_id'=>$wallet->id,
                'type'=>'withdraw',
                'amount'=>$amount,
                'confirmed'=>1
            ]
        );
        return [
            'from_type' => '\App\Models\Wallet\Wallet',
            'from_id' => $wallet->id,
            'to_type' => '\App\Models\Wallet\Wallet',
            'to_id' => $wallet2->id,
            'status'=>'transfer',
            'deposit_id'=>$tran1->id,
            'withdraw_id'=>$tran2->id,
        ];
    }
}

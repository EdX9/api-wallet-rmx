<?php

namespace Database\Factories\Wallet;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        
        return [
            'payable_type'=>"App\Models\User",
            'payable_id'=>1,
            'wallet_id'=>1,
            'type'=>'deposit',
            'amount'=>$this->faker->numberBetween(),
            'confirmed'=>1
        ];
    }
}

<?php

namespace Database\Factories\Wallet;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet\Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name= $this->faker->firstName();
        return [
            'holder_type' => "App\Models\User",
            'holder_id' => 1,
            'name'=>'WalletTest'.$name,
            'slug'=>'Wallet-Slug'.$name,
        ];
    }
}

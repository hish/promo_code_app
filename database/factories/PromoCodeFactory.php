<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Enums\PromoCodeType;
use App\Models\PromoCode;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromoCode>
 */
class PromoCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(5)),
            'type' => PromoCodeType::PERCENTAGE,
            'amount' => $this->faker->numberBetween(1, 20),
            'usage' => 0,
            'max_usage' => $this->faker->numberBetween(10, 100),
            'user_max_usage' => $this->faker->numberBetween(1, 5),
            'expires_at' => now()->addDays($this->faker->numberBetween(5, 30)),
        ];
    }
}

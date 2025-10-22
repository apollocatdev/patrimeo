<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => fake()->randomElement(['USD', 'EUR', 'GBP', 'JPY']),
            'main' => false,
        ];
    }

    /**
     * Indicate that the currency is the main currency.
     */
    public function main(): static
    {
        return $this->state(fn(array $attributes) => [
            'main' => true,
        ]);
    }
}

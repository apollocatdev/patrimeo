<?php

namespace Database\Factories;

use App\Enums\CotationUpdateMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cotation>
 */
class CotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['AAPL', 'MSFT', 'GOOGL', 'TSLA']),
            'isin' => fake()->regexify('[A-Z]{2}[A-Z0-9]{9}[0-9]'),
            'value' => fake()->randomFloat(2, 10, 1000),
            'value_main_currency' => fake()->randomFloat(2, 10, 1000),
            'last_update' => now(),
            'update_method' => CotationUpdateMethod::YAHOO,
            'update_data' => [],
            'user_id' => null,
        ];
    }

    /**
     * Indicate that the cotation uses OpenAI method.
     */
    public function openai(): static
    {
        return $this->state(fn(array $attributes) => [
            'update_method' => CotationUpdateMethod::OPENAI,
            'update_data' => [
                'prompt' => 'Get the latest price of {cotation_name}'
            ],
        ]);
    }
}

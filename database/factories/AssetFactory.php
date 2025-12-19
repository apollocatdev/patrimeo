<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetClass;
use App\Models\Envelop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'envelop_id' => Envelop::factory(),
            'class_id' => AssetClass::factory(),
            'quantity' => fake()->randomFloat(2, 0, 1000),
            'last_update' => fake()->dateTime(),
            'user_id' => User::factory(),
        ];
    }
}

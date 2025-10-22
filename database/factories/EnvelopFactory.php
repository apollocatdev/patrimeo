<?php

namespace Database\Factories;

use App\Models\Envelop;
use App\Models\EnvelopType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Envelop>
 */
class EnvelopFactory extends Factory
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
            'type_id' => EnvelopType::factory(),
            'user_id' => User::factory(),
        ];
    }
}

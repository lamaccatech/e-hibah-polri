<?php

namespace Database\Factories;

use App\Models\Donor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChangeHistory>
 */
class ChangeHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'changeable_type' => Donor::class,
            'changeable_id' => Donor::factory(),
            'change_reason' => fake()->sentence(),
            'state_before' => ['nama' => fake()->word()],
            'state_after' => ['nama' => fake()->word()],
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrantBudgetPlan>
 */
class GrantBudgetPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uraian' => fake()->sentence(3),
            'nilai' => fake()->randomFloat(2, 100000, 10000000),
        ];
    }
}

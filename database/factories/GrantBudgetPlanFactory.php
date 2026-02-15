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
            'volume' => fake()->randomFloat(2, 1, 100),
            'satuan' => fake()->randomElement(['unit', 'paket', 'bulan', 'orang']),
            'harga_satuan' => fake()->randomFloat(2, 100000, 10000000),
        ];
    }
}

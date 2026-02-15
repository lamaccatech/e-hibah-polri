<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrantAssessmentContent>
 */
class GrantAssessmentContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subjudul' => '',
            'isi' => fake()->paragraph(),
            'nomor_urut' => fake()->numberBetween(1, 5),
        ];
    }
}

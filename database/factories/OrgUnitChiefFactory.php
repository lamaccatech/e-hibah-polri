<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrgUnitChief>
 */
class OrgUnitChiefFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_lengkap' => fake()->name(),
            'jabatan' => fake()->jobTitle(),
            'pangkat' => fake()->randomElement(['Brigadir', 'Inspektur', 'Komisaris', 'Ajun Komisaris Besar']),
            'nrp' => fake()->unique()->numerify('########'),
            'sedang_menjabat' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'sedang_menjabat' => true,
        ]);
    }
}

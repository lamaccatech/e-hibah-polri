<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donor>
 */
class DonorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->company(),
            'asal' => fake()->randomElement(['DALAM NEGERI', 'LUAR NEGERI']),
            'alamat' => fake()->address(),
            'negara' => fake()->country(),
            'nomor_telepon' => fake()->phoneNumber(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\UnitLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrgUnit>
 */
class OrgUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->lexify('????'),
            'nama_unit' => fake()->company(),
            'level_unit' => UnitLevel::SatuanKerja->value,
        ];
    }

    public function mabes(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_unit' => UnitLevel::Mabes->value,
        ]);
    }

    public function satuanInduk(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_unit' => UnitLevel::SatuanInduk->value,
        ]);
    }

    public function satuanKerja(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_unit' => UnitLevel::SatuanKerja->value,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\GrantForm;
use App\Enums\GrantStage;
use App\Enums\GrantType;
use App\Models\Donor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grant>
 */
class GrantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pemberi_hibah' => Donor::factory(),
            'nama_hibah' => fake()->sentence(3),
            'jenis_hibah' => GrantType::Direct->value,
            'tahapan' => GrantStage::Planning->value,
            'bentuk_hibah' => GrantForm::Money->value,
        ];
    }

    public function withoutDonor(): static
    {
        return $this->state(fn () => [
            'id_pemberi_hibah' => null,
        ]);
    }

    public function planned(): static
    {
        return $this->state(fn () => [
            'jenis_hibah' => GrantType::Planned->value,
        ]);
    }
}

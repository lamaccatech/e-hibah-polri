<?php

namespace Database\Factories;

use App\Enums\AssessmentAspect;
use App\Enums\GrantStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrantAssessment>
 */
class GrantAssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(3),
            'aspek' => AssessmentAspect::Technical->value,
            'tahapan' => GrantStage::Planning->value,
        ];
    }
}

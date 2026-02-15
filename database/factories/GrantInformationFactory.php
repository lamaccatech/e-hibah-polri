<?php

namespace Database\Factories;

use App\Enums\GrantStage;
use App\Enums\ProposalChapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrantInformation>
 */
class GrantInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => ProposalChapter::General->value,
            'tahapan' => GrantStage::Planning->value,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\GrantStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrantStatusHistory>
 */
class GrantStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
        ];
    }
}

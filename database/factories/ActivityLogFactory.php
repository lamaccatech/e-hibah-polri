<?php

namespace Database\Factories;

use App\Enums\LogAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(LogAction::cases());

        return [
            'user_id' => User::factory(),
            'action' => $action,
            'message' => "{$action->label()} data uji coba",
        ];
    }
}

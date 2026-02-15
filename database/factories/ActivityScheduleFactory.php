<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivitySchedule>
 */
class ActivityScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 month', '+6 months');
        $end = fake()->dateTimeBetween($start, '+12 months');

        return [
            'uraian_kegiatan' => fake()->sentence(4),
            'tanggal_mulai' => $start->format('Y-m-d'),
            'tanggal_selesai' => $end->format('Y-m-d'),
        ];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Scenario 2: Base data + submitted grant plan ready for Polda review.
 *
 * Usage: php artisan migrate:fresh --seed --seeder=DemoPoldaScenarioSeeder
 *
 * Demo login:
 * - Satker (create new plans): polrestabandaaceh640122@polri.go.id / password
 * - Polda (review submitted):  poldaaceh@polri.go.id / password
 */
class DemoPoldaScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            DemoGrantPlanningSeeder::class,
        ]);
    }
}

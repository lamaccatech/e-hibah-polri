<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Scenario 3: Base data + Polda-verified grant plan ready for Mabes review.
 *
 * Usage: php artisan migrate:fresh --seed --seeder=DemoMabesScenarioSeeder
 *
 * Demo login:
 * - Satker (create new plans): polrestabandaaceh640122@polri.go.id / password
 * - Polda (review history):    poldaaceh@polri.go.id / password
 * - Mabes (review grant):      mabes@polri.go.id / password
 */
class DemoMabesScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            DemoGrantPlanningSeeder::class,
            DemoPoldaReviewSeeder::class,
        ]);
    }
}

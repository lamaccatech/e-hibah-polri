<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Scenarios:
     *  1. Default: php artisan migrate:fresh --seed
     *     → Clean slate. Login as any Satker to create a grant plan from scratch.
     *
     *  2. With demo data: php artisan migrate:fresh --seed --seeder=DemoScenarioSeeder
     *     → Includes a fully submitted grant plan from POLRESTA BANDA ACEH.
     *     → Login as POLDA ACEH (poldaaceh@polri.go.id / password) to review it.
     *
     *  3. Mabes review: php artisan migrate:fresh --seed --seeder=DemoMabesScenarioSeeder
     *     → Includes a Polda-verified grant plan ready for Mabes review.
     *     → Login as Mabes (mabes@polri.go.id / password) to review it.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            OrgUnitSeeder::class,
            AutocompleteSeeder::class,
            TagSeeder::class,
        ]);
    }
}

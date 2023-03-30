<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReportTestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(
            [
                CountrySeeder::class,
                CreateRoleSeeder::class,
                CreateAdminSeeder::class,
                UserSeeder::class,
                ConferenceSeeder::class,
                PermissionSeeder::class,
                ModelPermissionsSeeder::class
            ]
        );
    }
}

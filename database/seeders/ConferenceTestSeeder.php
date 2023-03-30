<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConferenceTestSeeder extends Seeder
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
                PermissionSeeder::class,
                ModelPermissionsSeeder::class
            ]
        );
    }
}

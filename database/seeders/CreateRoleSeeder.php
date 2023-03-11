<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(
            [
            'name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
            ]
        );

        Role::create(
            [
            'name' => 'Listener',
            'created_at' => now(),
            'updated_at' => now(),
            ]
        );

        Role::create(
            [
            'name' => 'Announcer',
            'created_at' => now(),
            'updated_at' => now(),
            ]
        );
    }
}

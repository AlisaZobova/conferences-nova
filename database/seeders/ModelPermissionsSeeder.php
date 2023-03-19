<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModelPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $announcer = Role::where('name', 'Announcer')->first();

        $create_conference = Permission::where('name', 'create conference')->first();

        $announcer->givePermissionTo($create_conference);
    }
}

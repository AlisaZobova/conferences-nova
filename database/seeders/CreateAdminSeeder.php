<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create(
            [
            'firstname' => 'Alex',
            'lastname' => 'Calm',
            'password' => Hash::make('12345678'),
            'birthdate' =>  '2000-01-01',
            'country_id' => 2,
            'phone' => '+1 (555) 555-5555',
            'email' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            ]
        );

        $admin->assignRole('Admin');
    }
}

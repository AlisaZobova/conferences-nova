<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::whereHas(
            'roles', function ($q) {
                $q->where('name', '!=', 'Admin');
            }
        )->get();

        foreach ($users as $user) {
            $user->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();
        }
    }
}

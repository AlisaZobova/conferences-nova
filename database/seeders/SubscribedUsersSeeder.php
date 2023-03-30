<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscribedUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create(
                [
                'email' => fake()->unique()->safeEmail(),
                'firstname' => fake()->firstname(),
                'lastname' => fake()->lastname(),
                'password' => Hash::make(12345678),
                'birthdate' => fake()->date(),
                'phone' => fake()->phoneNumber(),
                'country_id' => fake()->numberBetween(1, 10),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                ]
            );
            $user->newSubscription('Basic', 'price_1MncqrDyniFMFJ6W8sOvCjRc')->create('pm_card_visa');
        }
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create(
                [
                'email' => fake()->unique()->safeEmail(),
                'firstname' => fake()->firstname(),
                'lastname' => fake()->lastname(),
                'password' => Hash::make(12345678),
                'birthdate' => fake()->date(),
                'phone' => fake()->phoneNumber(),
                'country_id' => fake()->numberBetween(1, 10),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                ]
            );

            try {
                $user
                    ->newSubscription('Basic', 'price_1MncqrDyniFMFJ6W8sOvCjRc')
                    ->create();
            } catch (IncompletePayment $exception) {
                error_log('Incomplete created.');
            }
        }
    }
}

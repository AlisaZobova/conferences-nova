<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Free',
                'stripe_plan' => 'price_1MncnEDyniFMFJ6WGZNAwRff',
                'price' => 0,
                'joins_per_month' => 1,
                'description' => '1 join per month'
            ],
            [
                'name' => 'Basic',
                'stripe_plan' => 'price_1MncqrDyniFMFJ6W8sOvCjRc',
                'price' => 15,
                'joins_per_month' => 5,
                'description' => '5 joins per month'
            ],
            [
                'name' => 'Standard',
                'stripe_plan' => 'price_1Mncs0DyniFMFJ6Wm280jhk6',
                'price' => 25,
                'joins_per_month' => 50,
                'description' => '50 joins per month'
            ],
            [
                'name' => 'Unlimited',
                'stripe_plan' => 'price_1MncscDyniFMFJ6W2Z4RwFaC',
                'price' => 100,
                'joins_per_month' => null,
                'description' => 'No limit on joins'
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}

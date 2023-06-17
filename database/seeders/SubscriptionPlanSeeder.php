<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
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
                'price' => 0,
                'duration' => 30 // 30 days for Free plan
            ],
            [
                'name' => 'Monthly',
                'price' => 10,
                'duration' => 30 // 30 days for Monthly plan
            ],
            [
                'name' => 'Yearly',
                'price' => 100,
                'duration' => 365 // 365 days for Yearly plan
            ]
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}

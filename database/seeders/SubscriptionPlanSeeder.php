<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * The faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create new faker instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

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
                'description' => implode("\n", $this->faker->paragraphs(rand(2, 5))),
                'price' => 0,
                'duration' => 30 // 30 days for Free plan
            ],
            [
                'name' => 'Monthly',
                'description' => implode("\n", $this->faker->paragraphs(rand(2, 5))),
                'price' => 10,
                'duration' => 30 // 30 days for Monthly plan
            ],
            [
                'name' => 'Yearly',
                'description' => implode("\n", $this->faker->paragraphs(rand(2, 5))),
                'price' => 100,
                'duration' => 365 // 365 days for Yearly plan
            ]
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}

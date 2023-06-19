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

    protected $stripe;

    /**
     * Create new faker and stripe instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();

        $this->stripe = new \Stripe\StripeClient(config('payment.stripe.app_secret'));
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Stripe product
        $product = $this->stripe->products->create([
            'name' => 'Subscription Plan',
        ]);

        $plans = [
            [
                'name' => 'Free',
                'description' => implode("\n", $this->faker->paragraphs(rand(2, 5))),
                'price' => 0,
                'recurring' => 'month' // Subscription bill every month
            ],
            [
                'name' => 'Monthly',
                'description' => implode("\n", $this->faker->paragraphs(rand(2, 5))),
                'price' => 10,
                'recurring' => 'month' // Subscription bill every month
            ],
            [
                'name' => 'Yearly',
                'description' => implode("\n", $this->faker->paragraphs(rand(2, 5))),
                'price' => 100,
                'recurring' => 'year' // Subscription bill every year
            ]
        ];

        foreach ($plans as $plan) {
            // Create Stripe price object
            $price = $this->stripe->prices->create([
                'unit_amount' => $plan['price'] * 100,
                'currency' => 'myr',
                'recurring' => ['interval' => $plan['recurring']],
                'product' => $product->id,
            ]);

            SubscriptionPlan::create($plan + [
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id
            ]);
        }
    }
}

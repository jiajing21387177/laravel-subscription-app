<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserSubscriptionController extends Controller
{

    /**
     * Stripe client instance
     *
     * @var \Stripe\StripeClient
     */
    private $stripe;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->stripe = new \Stripe\StripeClient(config('payment.stripe.app_secret'));
    }

    /**
     * Create Stripe customer for user and create a checkout session for subscription.
     *
     * @param Request $request subscription_plans' id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function subscribe(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $validator->after(function ($validator) use ($user) {
            // Check if the user is already subscribed to the plan
            if (!empty($user->subscription) && in_array($user->subscription->payment_status, ['pending', 'paid'])) {
                $validator->errors()->add('subscription', __('validation.already_subscribed'));
            }
        });

        // Redirect user back to previous page with the validation errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get the selected plan from the form submission
        $plan = SubscriptionPlan::find($request->input('plan_id'));

        // Create Stripe customer object if null
        if (!$user->stripe_customer_id) {
            $user = $this->createStripeCustomerObject($user);
        }

        $checkoutSession = $this->createStripeCheckoutSession($user, $plan);

        // Redirect user to payment page
        return redirect($checkoutSession->url);
    }

    /**
     * Function to unsubscribe Stripe's subscription for user.
     *
     * @param Request $request user_subscriptions' id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsubscribe(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_subscription_id' => 'required|exists:user_subscriptions,id',
        ]);

        // Redirect user back to previous page with the validation errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userSubscription = UserSubscription::find($request->input('user_subscription_id'));

        $user = auth()->user();

        abort_if($userSubscription->user_id !== $user->id, '403', 'Permission denied.');
        abort_if(!$userSubscription->stripe_subscription_id, '400', 'Fail to unsubscribe.');

        // Cancel subscription
        if ($userSubscription->is_canceled === 0) {
            $subscription = $this->stripe->subscriptions->cancel(
                $userSubscription->stripe_subscription_id,
                []
            );

            $userSubscription->is_canceled = 1;
            $userSubscription->save();
        }

        return redirect('/profile');
    }

    /**
     * Create Stripe customer object
     *
     * @param User $user The user to create customer object.
     * @return User $user
     * @link https://stripe.com/docs/api/customers
     */
    protected function createStripeCustomerObject(User $user): User
    {
        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
        ]);
        $user->stripe_customer_id = $customer->id;
        $user->save();
        $user->refresh();
        return $user;
    }

    /**
     * Create a Stripe checkout session object.
     *
     * @param User $user User who want to subscribe a plan.
     * @param SubscriptionPlan $plan The plan which user want to subscribe.
     * @return \Stripe\Checkout\Session
     * @link https://stripe.com/docs/api/checkout/sessions/object
     */
    protected function createStripeCheckoutSession(User $user, SubscriptionPlan $plan)
    {
        $checkoutSession = $this->stripe->checkout->sessions->create([
            'customer' => $user->stripe_customer_id,
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => config('app.url') . "/subscribe/success",
            'cancel_url' => config('app.url') . "/subscribe/cancel",
        ]);

        return $checkoutSession;
    }
}

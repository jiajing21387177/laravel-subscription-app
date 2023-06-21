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

    public function subscribe(Request $request)
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

    public function unsubscribe(Request $request)
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
        // Save the subscription record into the database
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'payment_status' => 'checkout',
        ]);

        $checkoutSession = $this->stripe->checkout->sessions->create([
            'customer' => $user->stripe_customer_id,
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => config('app.url') . "/subscribe/success?user_subscription_id=$subscription->id",
            'cancel_url' => config('app.url') . "/subscribe/cancel?user_subscription_id=$subscription->id",
        ]);

        $subscription->stripe_checkout_session_id = $checkoutSession->id;
        $subscription->save();

        return $checkoutSession;
    }

    /**
     * Handle the successful completion of a checkout session for a user subscription.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function checkoutSuccess(Request $request)
    {
        $payload = UserSubscription::getUserSubscriptionWithCheckoutSession((int) $request->input('user_subscription_id'));

        if ($payload instanceof \Illuminate\Http\RedirectResponse) {
            return $payload;
        }

        $checkoutSession = $this->stripe->checkout->sessions->retrieve(
            $payload->stripe_checkout_session_id,
            []
        );

        abort_if($checkoutSession->status !== 'complete', 400, 'Subscription failed.');

        switch ($checkoutSession->payment_status) {
            case 'paid':
            case 'no_payment_required':
                $payload->payment_status = 'paid';
                break;
            case 'unpaid':
                $payload->payment_status = 'pending';
                break;
        }

        $subscription = $this->stripe->subscriptions->retrieve(
            $checkoutSession->subscription,
            []
        );

        $payload->stripe_subscription_id = $subscription->id;
        $payload->subscription_start_datetime = gmdate('Y-m-d H:i:s', $subscription->current_period_start);
        $payload->subscription_end_datetime = gmdate('Y-m-d H:i:s', $subscription->current_period_end);

        $payload->save();

        return redirect('/profile');
    }

    /**
     * Cancel the checkout process for a user subscription.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function checkoutCancel(Request $request)
    {
        $payload = UserSubscription::getUserSubscriptionWithCheckoutSession((int) $request->input('user_subscription_id'));

        if ($payload instanceof \Illuminate\Http\RedirectResponse) {
            return $payload;
        }

        $user = auth()->user();

        abort_if($user->id !== $payload->user_id, 403, 'Fail to cancel checkout.');

        $checkoutSession = $this->stripe->checkout->sessions->expire(
            $payload->stripe_checkout_session_id,
            []
        );

        if ($checkoutSession->status === 'expired') {
            $payload->delete();
        }

        return redirect('/profile');
    }
}

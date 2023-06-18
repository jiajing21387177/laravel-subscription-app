<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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

        if (!$user->stripe_customer_id) {
            // TODO: Create Stripe customer object
        }

        // Save the subscription record into the database
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'subscription_start_datetime' => new DateTime(),
            'subscription_end_datetime' => (new DateTime())->modify("+$plan->duration days"),
            'payment_status' => 'pending'
        ]);

        // TODO: Create Stripe subscription object
        $url = 'https://the.stripe.url';

        // Redirect user to payment page
        return redirect($url);
    }
}

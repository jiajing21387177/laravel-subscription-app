<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UserSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'subscription_plan_id', 'subscription_start_datetime',
        'subscription_end_datetime', 'stripe_checkout_session_id', 'stripe_subscription_id',
        'payment_status', 'is_canceled'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'stripe_checkout_session_id', 'stripe_subscription_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'subscription_start_datetime' => 'datetime',
        'subscription_end_datetime' => 'datetime',
    ];

    /**
     * Get the user subscription along with the associated checkout session.
     *
     * @param int $id The ID of the user subscription
     * @return mixed The user subscription with checkout session if found, otherwise redirects back with errors
     */
    public static function getUserSubscriptionWithCheckoutSession(int $id)
    {
        $validator = Validator::make(['user_subscription_id' => $id], [
            'user_subscription_id' => 'required|exists:user_subscriptions,id',
        ]);

        // Redirect user back to the previous page with the validation errors
        if ($validator->fails()) {
            return redirect('/profile')->withErrors($validator)->withInput();
        }

        $userSubscription = UserSubscription::find($id);

        abort_if(empty($userSubscription->stripe_checkout_session_id), 400, 'Checkout session not found.');

        return $userSubscription;
    }
}

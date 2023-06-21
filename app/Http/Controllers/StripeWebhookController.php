<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Stripe\Event;
use Stripe\Stripe;

class StripeWebhookController extends Controller
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

    public function handleWebhook(Request $request)
    {
        // Retrieve the event data from the request
        $payload = $request->getContent();

        // Verify the Stripe webhook signature
        $signature = $request->header('Stripe-Signature');
        $endpointSecret = config('payment.stripe.webhook_secret');
        try {
            $event = Event::constructFrom(
                json_decode($payload, true),
                $signature,
                $endpointSecret
            );
        } catch (\Exception $e) {
            return response('Webhook signature verification failed.', 400);
        }

        // Handle specific webhook event types
        switch ($event->type) {
            case 'invoice.created':
                $this->handleSubscriptionInvoiceCreated($event);
                break;
            case 'invoice.payment_succeeded':
                $this->handleSubscriptionPaymentSucceed($event);
                break;
            case 'invoice.payment_failed':
                $this->handleSubscriptionPaymentFail($event);
                break;
                // Handle other event types as needed
            default:
                // Unsupported event type
                return response('Unsupported event type', 400);
        }

        return response('Webhook received successfully');
    }

    private function handleSubscriptionInvoiceCreated(Event $event)
    {
        $subscription = $this->stripe->subscriptions->retrieve($event->data->object->subscription);

        $plan = SubscriptionPlan::where('stripe_price_id', $subscription->items->data[0]->price->id)
            ->first();

        abort_if(!$plan, 404, 'Subscription plan not found.');

        $user = User::where('stripe_customer_id', $subscription->customer)->first();

        abort_if(!$plan, 404, 'User not found.');

        UserSubscription::updateOrCreate(
            [
                'stripe_invoice_id' => $event->data->object->id,
                'stripe_subscription_id' => $subscription->id,
            ],
            [
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'subscription_start_datetime' => gmdate('Y-m-d H:i:s', $subscription->current_period_start),
                'subscription_end_datetime' => gmdate('Y-m-d H:i:s', $subscription->current_period_end),
                'payment_status' => 'pending',
            ]
        );
    }

    private function handleSubscriptionPaymentSucceed(Event $event)
    {
        UserSubscription::where(['stripe_invoice_id' => $event->data->object->id])
            ->update(['payment_status' => 'paid']);
    }

    private function handleSubscriptionPaymentFail(Event $event)
    {
        $invoice = $event->data->object->id;
        UserSubscription::where(['stripe_invoice_id' => $event->data->object->id])
            ->update(['payment_status' => 'failed']);
    }
}

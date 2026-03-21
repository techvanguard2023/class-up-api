<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\WebhookEndpoint;

class StripeWebhookController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // Verify webhook signature
            $event = Event::constructFrom(
                json_decode($payload, true)
            );

            // Verify signature
            \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Handle event
        match($event->type) {
            'customer.subscription.created' => $this->handleSubscriptionCreated($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event),
            'invoice.payment_failed' => $this->handlePaymentFailed($event),
            default => null,
        };

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription created event.
     */
    private function handleSubscriptionCreated(Event $event): void
    {
        $stripeSubscription = $event->data->object;
        $metadata = $stripeSubscription->metadata ?? null;

        // Get user and plan from metadata
        if (!$metadata || !isset($metadata->user_id, $metadata->plan_id)) {
            return;
        }

        $user = User::find($metadata->user_id);
        $plan = Plan::find($metadata->plan_id);

        if (!$user || !$plan) {
            return;
        }

        // Extract price ID from subscription items
        $priceId = $stripeSubscription->items->data[0]->price->id ?? null;

        // Create or update subscription
        Subscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $user->school_id,
            ],
            [
                'plan_id' => $plan->id,
                'stripe_customer_id' => $stripeSubscription->customer,
                'stripe_subscription_id' => $stripeSubscription->id,
                'stripe_price_id' => $priceId,
                'status' => $stripeSubscription->status === 'active' ? 'active' : 'active',
                'payment_method' => 'stripe',
                'starts_at' => now(),
                'ends_at' => $stripeSubscription->current_period_end ? now()->timestamp($stripeSubscription->current_period_end) : null,
            ]
        );
    }

    /**
     * Handle subscription updated event.
     */
    private function handleSubscriptionUpdated(Event $event): void
    {
        $stripeSubscription = $event->data->object;

        $subscription = Subscription::where(
            'stripe_subscription_id',
            $stripeSubscription->id
        )->first();

        if (!$subscription) {
            return;
        }

        // Update subscription details
        $subscription->update([
            'status' => $stripeSubscription->status === 'active' ? 'active' : $stripeSubscription->status,
            'ends_at' => $stripeSubscription->current_period_end
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : null,
            'cancel_at_period_end' => $stripeSubscription->cancel_at_period_end ?? false,
        ]);

        // If subscription is now unpaid/incomplete, mark as past_due
        if ($stripeSubscription->status === 'incomplete' || $stripeSubscription->status === 'incomplete_expired') {
            $subscription->update(['status' => 'past_due']);
        }
    }

    /**
     * Handle subscription deleted event.
     */
    private function handleSubscriptionDeleted(Event $event): void
    {
        $stripeSubscription = $event->data->object;

        $subscription = Subscription::where(
            'stripe_subscription_id',
            $stripeSubscription->id
        )->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'canceled',
            'ends_at' => now(),
        ]);
    }

    /**
     * Handle payment succeeded event.
     */
    private function handlePaymentSucceeded(Event $event): void
    {
        $invoice = $event->data->object;

        if (!$invoice->subscription) {
            return;
        }

        $subscription = Subscription::where(
            'stripe_subscription_id',
            $invoice->subscription
        )->first();

        if (!$subscription) {
            return;
        }

        // Payment succeeded - mark as active
        $subscription->update([
            'status' => 'active',
            'payment_method' => 'stripe',
        ]);
    }

    /**
     * Handle payment failed event.
     */
    private function handlePaymentFailed(Event $event): void
    {
        $invoice = $event->data->object;

        if (!$invoice->subscription) {
            return;
        }

        $subscription = Subscription::where(
            'stripe_subscription_id',
            $invoice->subscription
        )->first();

        if (!$subscription) {
            return;
        }

        // Payment failed - mark as past_due
        $subscription->update([
            'status' => 'past_due',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe checkout session.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $user = $request->user();

        // Check if plan has Stripe price ID
        if (!$plan->stripe_price_id) {
            return response()->json([
                'error' => 'Plan not available for purchase',
                'code' => 'PLAN_NOT_AVAILABLE',
            ], 400);
        }

        try {
            // Create Stripe checkout session
            $session = $this->stripe->checkout->sessions->create([
                'customer_email' => $user->email,
                'line_items' => [
                    [
                        'price' => $plan->stripe_price_id,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'subscription',
                'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.cancel'), // named route points to checkoutCanceled method
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'school_id' => $user->school_id,
                ],
            ]);

            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);
        }
        catch (ApiErrorException $e) {
            return response()->json([
                'error' => 'Failed to create checkout session',
                'code' => 'STRIPE_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current subscription status.
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'active' => false,
                'message' => 'No active subscription',
            ]);
        }

        $plan = $subscription->plan->load(['features' => fn($q) => $q->select('features.id', 'features.name')]);

        return response()->json([
            'active' => $subscription->isActive() || $subscription->isTrial(),
            'status' => $subscription->status,
            'is_trial' => $subscription->isTrial(),
            'is_expired' => $subscription->isExpired(),
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
            ],
            'features' => $plan->features->pluck('name'),
            'started_at' => $subscription->starts_at,
            'expires_at' => $subscription->ends_at,
            'trial_ends_at' => $subscription->trial_ends_at,
            'cancel_at_period_end' => $subscription->cancel_at_period_end,
            'payment_method' => $subscription->payment_method,
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription || $subscription->isExpired()) {
            return response()->json([
                'error' => 'Subscription not found or already canceled',
                'code' => 'SUBSCRIPTION_NOT_FOUND',
            ], 404);
        }

        try {
            // Cancel at end of period
            $this->stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
            ['cancel_at_period_end' => true]
            );

            $subscription->update(['cancel_at_period_end' => true]);

            return response()->json([
                'message' => 'Subscription will be canceled at the end of the billing period',
                'expires_at' => $subscription->ends_at,
            ]);
        }
        catch (ApiErrorException $e) {
            return response()->json([
                'error' => 'Failed to cancel subscription',
                'code' => 'STRIPE_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List Stripe invoices for the current user's subscription.
     */
    public function invoices(Request $request)
    {
        $subscription = $request->user()->subscription;

        if (!$subscription || !$subscription->stripe_customer_id) {
            return response()->json(['invoices' => []]);
        }

        try {
            $stripeInvoices = $this->stripe->invoices->all([
                'customer' => $subscription->stripe_customer_id,
                'limit' => 24,
            ]);

            $invoices = collect($stripeInvoices->data)->map(fn($invoice) => [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'status' => $invoice->status,
            'amount_paid' => $invoice->amount_paid / 100,
            'amount_due' => $invoice->amount_due / 100,
            'currency' => strtoupper($invoice->currency),
            'period_start' => date('Y-m-d', $invoice->period_start),
            'period_end' => date('Y-m-d', $invoice->period_end),
            'invoice_pdf' => $invoice->invoice_pdf,
            'hosted_invoice_url' => $invoice->hosted_invoice_url,
            'created_at' => date('Y-m-d H:i:s', $invoice->created),
            ]);

            return response()->json(['invoices' => $invoices]);
        }
        catch (ApiErrorException $e) {
            return response()->json([
                'error' => 'Failed to fetch invoices',
                'code' => 'STRIPE_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resume canceled subscription.
     */
    public function resume(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->cancel_at_period_end) {
            return response()->json([
                'error' => 'Subscription is not scheduled for cancellation',
                'code' => 'SUBSCRIPTION_NOT_CANCELABLE',
            ], 400);
        }

        try {
            $this->stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
            ['cancel_at_period_end' => false]
            );

            $subscription->update(['cancel_at_period_end' => false]);

            return response()->json([
                'message' => 'Subscription resumption scheduled',
                'expires_at' => $subscription->ends_at,
            ]);
        }
        catch (ApiErrorException $e) {
            return response()->json([
                'error' => 'Failed to resume subscription',
                'code' => 'STRIPE_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle successful checkout session
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        if (!$sessionId) {
            return redirect($frontendUrl . '/subscription/failed?error=Missing+session_id');
        }

        try {
            // Retrieve the checkout session from Stripe
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);

            // Get user from metadata
            $userId = $session->metadata->user_id ?? null;
            $planId = $session->metadata->plan_id ?? null;
            $schoolId = $session->metadata->school_id ?? null;

            if (!$userId || !$planId) {
                return redirect($frontendUrl . '/subscription/failed?error=Invalid+session+metadata');
            }

            $user = \App\Models\User::findOrFail($userId);

            // Check if payment was successful
            if ($session->payment_status !== 'paid') {
                return redirect($frontendUrl . '/subscription/failed?error=Payment+not+completed');
            }

            // Get the subscription from Stripe
            $stripeSubscription = $this->stripe->subscriptions->retrieve($session->subscription);

            // Get plan from database
            $plan = Plan::findOrFail($planId);

            // Create subscription in database
            Subscription::create([
                'user_id' => $userId,
                'plan_id' => $planId,
                'school_id' => $schoolId,
                'status' => $stripeSubscription->status,
                'stripe_customer_id' => $session->customer,
                'stripe_subscription_id' => $session->subscription,
                'stripe_price_id' => $plan->stripe_price_id,
                'starts_at' => now(),
                'ends_at' => $stripeSubscription->current_period_end ? now()->setTimestamp($stripeSubscription->current_period_end) : null,
                'payment_method' => 'stripe',
            ]);

            // Redirect to dashboard on success
            return redirect($frontendUrl . '/?subscription=success');
        }
        catch (ApiErrorException $e) {
            return redirect($frontendUrl . '/subscription/failed?error=' . urlencode($e->getMessage()));
        }
        catch (\Exception $e) {
            return redirect($frontendUrl . '/subscription/failed?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle canceled checkout session
     */
    public function checkoutCanceled(Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return redirect($frontendUrl . '/subscription/canceled');
    }
}

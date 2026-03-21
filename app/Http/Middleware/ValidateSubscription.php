<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  $feature
     */
    public function handle(Request $request, Closure $next, ?string $feature = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Get user's active subscription
        $subscription = $user->subscription;

        // Check if subscription exists and is active
        if (!$subscription || (!$subscription->isActive() && !$subscription->isTrial())) {
            return response()->json([
                'error' => 'No active subscription',
                'code' => 'SUBSCRIPTION_INACTIVE',
                'message' => 'Your subscription is inactive or expired. Please upgrade your plan.',
            ], 403);
        }

        // Check if user has access to specific feature (if required)
        if ($feature) {
            if (!$subscription->hasFeature($feature)) {
                return response()->json([
                    'error' => "Feature '{$feature}' not available",
                    'code' => 'FEATURE_UNAVAILABLE',
                    'message' => "This feature is not included in your current plan. Please upgrade to access it.",
                    'feature' => $feature,
                ], 403);
            }

            // Check if feature limit has not been exceeded
            if (!$subscription->canUseFeature($feature)) {
                $available = $subscription->getFeatureAvailable($feature);
                return response()->json([
                    'error' => "Feature '{$feature}' limit exceeded",
                    'code' => 'FEATURE_LIMIT_EXCEEDED',
                    'message' => 'You have reached the limit for this feature this month.',
                    'feature' => $feature,
                    'available' => $available,
                ], 403);
            }
        }

        return $next($request);
    }
}

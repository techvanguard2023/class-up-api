<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Subscription extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'payment_method',
        'external_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'cancel_at_period_end',
    ];

    protected $casts = [
        'starts_at'             => 'datetime',
        'ends_at'               => 'datetime',
        'trial_ends_at'         => 'datetime',
        'cancel_at_period_end'  => 'boolean',
    ];

    /**
     * The user that owns this subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The plan linked to this subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Feature usage tracking.
     */
    public function featureUsage()
    {
        return $this->hasMany(FeatureUsage::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Check if subscription is currently active.
     */
    public function isActive(): bool
    {
        if ($this->status === 'active' && $this->ends_at) {
            return $this->ends_at > now();
        }

        return $this->status === 'active';
    }

    /**
     * Check if subscription is in trial period.
     */
    public function isTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at > now();
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return ($this->status === 'expired' || $this->status === 'canceled')
            || ($this->ends_at && $this->ends_at <= now());
    }

    /**
     * Check if user has access to feature.
     */
    public function hasFeature(string $featureName): bool
    {
        if (!$this->isActive() && !$this->isTrial()) {
            return false;
        }

        return $this->plan
            ->features()
            ->where('name', $featureName)
            ->exists();
    }

    /**
     * Check if user can use a feature (considering limits).
     */
    public function canUseFeature(string $featureName): bool
    {
        // Must have feature and active subscription
        if (!$this->hasFeature($featureName)) {
            return false;
        }

        $feature = Feature::where('name', $featureName)->first();
        if (!$feature) {
            return false;
        }

        // Get the limit for this feature in the current plan
        $limit = $this->plan->getFeatureLimit($featureName);

        // NULL = unlimited
        if ($limit === null) {
            return true;
        }

        // Check current usage this month
        $usage = $this->featureUsage()
            ->where('feature_id', $feature->id)
            ->where('month', now()->format('Y-m'))
            ->first();

        return ($usage?->count ?? 0) < $limit;
    }

    /**
     * Get available count for a feature in current month.
     */
    public function getFeatureAvailable(string $featureName): ?int
    {
        if (!$this->hasFeature($featureName)) {
            return null;
        }

        $limit = $this->plan->getFeatureLimit($featureName);

        // Unlimited
        if ($limit === null) {
            return -1;
        }

        $feature = Feature::where('name', $featureName)->first();
        $usage = $this->featureUsage()
            ->where('feature_id', $feature->id)
            ->where('month', now()->format('Y-m'))
            ->first();

        return $limit - ($usage?->count ?? 0);
    }

    /**
     * Increment feature usage.
     */
    public function incrementFeatureUsage(string $featureName, int $count = 1): void
    {
        $feature = Feature::where('name', $featureName)->first();
        if (!$feature) {
            return;
        }

        $month = now()->format('Y-m');

        FeatureUsage::updateOrCreate(
            [
                'subscription_id' => $this->id,
                'feature_id' => $feature->id,
                'month' => $month,
            ],
            [
                'count' => \DB::raw("count + {$count}"),
            ]
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'active',
        'color',
        'stripe_price_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * The features included in this plan.
     */
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_plans')
                    ->withPivot('limit')
                    ->withTimestamps();
    }

    /**
     * Get a specific feature limit for this plan.
     */
    public function getFeatureLimit($featureName)
    {
        return $this->features()
                    ->where('features.name', $featureName)
                    ->first()?->pivot?->limit;
    }
}

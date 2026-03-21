<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureUsage extends Model
{
    use HasFactory;

    protected $table = 'feature_usage';

    protected $fillable = [
        'subscription_id',
        'feature_id',
        'count',
        'month',
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    /**
     * The subscription this usage belongs to.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * The feature being tracked.
     */
    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}

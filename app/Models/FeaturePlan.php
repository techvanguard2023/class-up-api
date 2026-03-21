<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'plan_id',
        'limit',
    ];

    protected $casts = [
        'limit' => 'integer',
    ];

    /**
     * The feature associated with this pivot record.
     */
    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * The plan associated with this pivot record.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}

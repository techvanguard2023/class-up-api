<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Plans that include this feature (via pivot).
     */
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'feature_plans')
                    ->withPivot('limit')
                    ->withTimestamps();
    }

    /**
     * Explicit pivot records.
     */
    public function featurePlans()
    {
        return $this->hasMany(FeaturePlan::class);
    }
}

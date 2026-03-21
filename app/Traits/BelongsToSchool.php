<?php

namespace App\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool()
    {
        static::creating(function ($model) {
            if (Auth::check() && !$model->school_id) {
                $model->school_id = Auth::user()->school_id;
            }
        });

        static::addGlobalScope('school', new class implements \Illuminate\Database\Eloquent\Scope {
            public function apply(Builder $builder, Model $model)
            {
                if (Auth::check()) {
                    $user = Auth::user();
                    if ($user && $user->school_id) {
                        $builder->where(function (Builder $query) use ($model, $user) {
                            $query->where($model->getTable() . '.school_id', $user->school_id);
                            
                            if (isset($model->includeSystemRecords) && $model->includeSystemRecords === true) {
                                $query->orWhereNull($model->getTable() . '.school_id');
                            }
                        });
                    }
                }
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

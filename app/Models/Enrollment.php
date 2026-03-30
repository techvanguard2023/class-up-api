<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Enrollment extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['school_id', 'student_id', 'classroom_id', 'status', 'year'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($enrollment) {
            $enrollment->classroom?->increment('enrolled');
        });

        static::deleted(function ($enrollment) {
            $enrollment->classroom?->decrement('enrolled');
        });

        static::restored(function ($enrollment) {
            $enrollment->classroom?->increment('enrolled');
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}

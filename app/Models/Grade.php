<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Grade extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['school_id', 'enrollment_id', 'subject_id', 'period', 'value', 'weight'];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}

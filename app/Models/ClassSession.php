<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    use HasFactory, BelongsToSchool, SoftDeletes;

    protected $table = 'class_sessions';

    protected $fillable = [
        'school_id',
        'name',
        'instructor_id',
        'classroom_id',
        'subject_id',
        'start_time',
        'end_time',
        'days',
        'modality',
        'color',
    ];

    protected $casts = [
        'days' => 'array',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class ClassSession extends Model
{
    use HasFactory, BelongsToSchool;

    protected $table = 'class_sessions';

    protected $fillable = [
        'school_id',
        'name',
        'teacher_id',
        'instructor_id',
        'classroom_id',
        'subject_id',
        'start_time',
        'end_time',
        'days',
        'capacity',
        'enrolled',
        'modality',
        'color',
    ];

    protected $casts = [
        'days' => 'array',
        'capacity' => 'integer',
        'enrolled' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

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

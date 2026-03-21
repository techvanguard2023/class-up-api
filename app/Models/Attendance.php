<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Attendance extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['school_id', 'class_session_id', 'student_id', 'date', 'status'];

    protected $casts = [
        'date' => 'date',
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

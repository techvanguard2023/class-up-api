<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Classroom extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['school_id', 'instructor_id', 'name', 'capacity', 'enrolled', 'year', 'shift', 'level'];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, Enrollment::class, 'classroom_id', 'id', 'id', 'student_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }
}

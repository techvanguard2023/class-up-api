<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'email',
        'phone',
        'qualification',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * The school that owns this instructor.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * The classrooms taught by this instructor.
     */
    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * The class sessions taught by this instructor.
     */
    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }
}

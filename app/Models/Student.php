<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Student extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'name',
        'user_id',
        'school_id',
        'modality',
        'outside_school_name',
        'level',
        'status',
        'next_class',
        'photo_url',
        'attendance_rate',
        'birth_date',
        'health_info',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'attendance_rate' => 'float',
        'health_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class , 'guardian_student')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function studentPaymentPlans()
    {
        return $this->hasMany(StudentPaymentPlan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

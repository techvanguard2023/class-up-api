<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToSchool;

class SchoolPaymentPlan extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'price',
        'due_day',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'due_day' => 'integer',
        'active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            StudentPaymentPlan::class,
            'school_payment_plan_id',
            'id',
            'id',
            'student_id'
        );
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

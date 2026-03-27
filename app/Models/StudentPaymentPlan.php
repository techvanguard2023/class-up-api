<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentPaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_payment_plan_id',
        'due_day',
        'start_date',
        'end_date',
        'active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolPaymentPlan()
    {
        return $this->belongsTo(SchoolPaymentPlan::class);
    }

    /**
     * Get the due day for this student payment plan.
     * Returns the student's custom due_day if set, otherwise the plan's default due_day
     */
    public function getDueDay()
    {
        return $this->due_day ?? $this->schoolPaymentPlan->due_day;
    }
}

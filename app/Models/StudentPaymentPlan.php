<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToSchool;

class StudentPaymentPlan extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'student_id',
        'school_payment_plan_id',
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

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

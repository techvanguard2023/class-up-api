<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'student_payment_plan_id',
        'guardian_id',
        'amount',
        'payment_date',
        'notes',
        'receipt_path',
        'status',
        'confirmed_by',
        'confirmed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentPaymentPlan()
    {
        return $this->belongsTo(StudentPaymentPlan::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}

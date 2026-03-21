<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_payment_plan_id',
        'payment_method_id',
        'amount',
        'due_date',
        'paid_date',
        'status',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolPaymentPlan()
    {
        return $this->belongsTo(SchoolPaymentPlan::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    // Methods
    public function markAsPaid($paidDate = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => $paidDate ?? now(),
        ]);

        return $this;
    }

    public function markAsLate()
    {
        $this->update([
            'status' => 'late',
        ]);

        return $this;
    }

    public function markAsCanceled()
    {
        $this->update([
            'status' => 'canceled',
        ]);

        return $this;
    }
}

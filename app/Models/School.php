<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_type_id',
        'owner_id',
        'name',
        'slug',
        'address',
        'phone',
        'logo_url',
        'active',
        'invite_code'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class , 'owner_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function schoolType()
    {
        return $this->belongsTo(SchoolType::class);
    }

    public function modalities()
    {
        return $this->hasMany(Modality::class);
    }

    public function schoolPaymentPlans()
    {
        return $this->hasMany(SchoolPaymentPlan::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Student::class);
    }
}

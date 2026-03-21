<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Guardian extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['school_id', 'user_id', 'cpf', 'phone'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }
}

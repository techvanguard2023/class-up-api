<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Subject extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'description'];

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }
}

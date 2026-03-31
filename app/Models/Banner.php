<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'school_id',
        'name',
        'description',
        'image_url',
        'status',
        'position',
        'link',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

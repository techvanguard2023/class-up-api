<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class CertificateTemplate extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'background_url',
        'content_json',
        'is_default',
    ];

    protected $casts = [
        'content_json' => 'array',
        'is_default' => 'boolean',
    ];
}

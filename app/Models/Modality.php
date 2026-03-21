<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Modality extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    /**
     * Whether to include system records (where school_id is null) in the global scope.
     *
     * @var bool
     */
    public $includeSystemRecords = true;

    protected $fillable = [
        'school_id',
        'name',
        'description',
    ];
}

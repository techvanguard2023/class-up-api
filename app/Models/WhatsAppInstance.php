<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class WhatsAppInstance extends Model
{
    use BelongsToSchool;

    protected $table = 'whatsapp_instances';

    protected $fillable = [
        'school_id',
        'instance_name',
        'instance_id',
        'api_key',
        'owner',
        'profile_name',
        'profile_picture_url',
        'profile_status',
        'status',
        'server_url',
        'integration',
        'webhook_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

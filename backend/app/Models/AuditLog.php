<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'audit_logs';

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'user_id',
        'user_name',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'metadata' => 'array',
    ];
}

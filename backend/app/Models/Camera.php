<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'code',
        'status',
        'connected_at',
        'disconnected_at',
        'last_maintenance_at',
        'yolo_detection_status',
        'yolo_service_url',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
            'last_maintenance_at' => 'datetime',
            'yolo_detection_status' => 'boolean',
        ];
    }
}

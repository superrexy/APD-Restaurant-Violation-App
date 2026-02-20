<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'camera_id',
        'image_path',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'image_path' => 'string',
        ];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function violationDetails(): HasMany
    {
        return $this->hasMany(ViolationDetail::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationType extends Model
{
    /** @use HasFactory<\Database\Factories\ViolationTypeFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'code', 'severity', 'is_active'];

    protected function casts(): array
    {
        return [
            'severity' => 'string',
            'is_active' => 'boolean',
        ];
    }
}

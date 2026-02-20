<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViolationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'violation_id',
        'violation_type_id',
        'confidence_score',
        'additional_info',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'float',
            'status' => 'string',
        ];
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardIndicator extends Model
{
    protected $fillable = [
        'quality_standard_id',
        'type',
        'text',
        'target',
        'sort_order',
    ];

    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class, 'quality_standard_id');
    }
}

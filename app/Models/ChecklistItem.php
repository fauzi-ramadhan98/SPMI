<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use LogsActivity;

    protected $fillable = [
        'quality_standard_id',
        'code',
        'indicator_key',
        'indicator',
        'audit_question',
        'rubric_4',
        'rubric_3',
        'rubric_2',
        'rubric_1',
        'evidence_document',
        'max_score',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_score' => 'integer',
        'sort_order' => 'integer',
    ];

    public function standard()
    {
        return $this->belongsTo(QualityStandard::class, 'quality_standard_id');
    }
}

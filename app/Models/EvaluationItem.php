<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationItem extends Model
{
    protected $fillable = [
        'evaluation_id',
        'quality_standard_id',
        'checklist_item_id',
        'criteria',
        'indicator',
        'score',
        'self_assessment',
        'narasi',
        'notes',
        'root_cause',
        'impact',
        'mitigation',
        'action_plan',
        'target_date',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function standard()
    {
        return $this->belongsTo(QualityStandard::class, 'quality_standard_id');
    }

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }

    public function attachments()
    {
        return $this->morphMany(EvaluationAttachment::class, 'attachable');
    }
}

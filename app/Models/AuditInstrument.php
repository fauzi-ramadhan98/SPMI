<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditInstrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_assignment_id',
        'checklist_item_id',
        'criteria',
        'indicator',
        'audit_question',
        'rubric_4',
        'rubric_3',
        'rubric_2',
        'rubric_1',
        'evidence_document',
        'score',
        'finding',
        'recommendation',
        'finding_category',
        'document_link',
        'findings_data',
    ];

    protected $casts = [
        'findings_data' => 'array',
    ];

    public function assignment()
    {
        return $this->belongsTo(AuditAssignment::class, 'audit_assignment_id');
    }
}

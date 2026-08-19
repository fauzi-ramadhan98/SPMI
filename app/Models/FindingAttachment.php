<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FindingAttachment extends Model
{
    protected $fillable = [
        'audit_finding_id',
        'title',
        'category',
        'file_path',
        'file_name',
        'link',
        'uploaded_by',
    ];

    public function finding()
    {
        return $this->belongsTo(AuditFinding::class, 'audit_finding_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

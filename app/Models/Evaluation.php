<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'academic_year',
        'semester',
        'evaluable_type',
        'evaluable_id',
        'status',
        'conclusion',
        'created_by',
    ];

    public function evaluable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(EvaluationItem::class)->orderBy('id');
    }

    public function attachments()
    {
        return $this->morphMany(EvaluationAttachment::class, 'attachable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Nama pemilik ED (prodi / unit).
     */
    public function getOwnerLabelAttribute(): string
    {
        return $this->evaluable?->name ?? '—';
    }

    public function getOwnerTypeLabelAttribute(): string
    {
        if ($this->evaluable_type === Unit::class) {
            return 'Unit Kerja';
        }
        if ($this->evaluable_type === AcademicProgram::class) {
            return 'Prodi';
        }
        return '—';
    }
}

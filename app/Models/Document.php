<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',            // Kode Dokumen (misal: DOK-001)
        'title',
        'document_type',
        'module',
        'document_category_id',
        'academic_year',
        'semester',
        'file_path',
        'file_name',
        'file_size',
        'is_public',
        'description',
        'uploaded_by',
        'academic_program_id',
        'unit_id',
        'audit_cycle_id',
        'standard_decree_id',
        'doc_date',
        'version',        // Nomor revisi dokumen (manual input)
        'status',        // draft | aktif
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'doc_date' => 'date',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function qualityStandards(): HasMany
    {
        return $this->hasMany(QualityStandard::class, 'document_id');
    }

    public function cycle()
    {
        return $this->belongsTo(AuditCycle::class, 'audit_cycle_id');
    }

    public function decree()
    {
        return $this->belongsTo(StandardDecree::class, 'standard_decree_id');
    }

    /**
     * Label atribusi dokumen: prodi, unit, atau institusi.
     */
    public function getOwnerLabelAttribute(): string
    {
        if ($this->academicProgram) {
            return 'Prodi: ' . $this->academicProgram->name;
        }
        if ($this->unit) {
            return 'Unit: ' . $this->unit->name;
        }
        return 'Institusi (Umum)';
    }
}
?>

<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityStandardApplicability extends Model
{
    use LogsActivity;

    protected $table = 'quality_standard_applicability';

    protected $fillable = [
        'quality_standard_id',
        'target_type',
        'academic_program_id',
        'unit_id',
    ];

    protected $casts = [
        'target_type' => 'string',
    ];

    public function standard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class, 'quality_standard_id');
    }

    public function academicProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'academic_program_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function logLabel(): string
    {
        return 'Apabilitas Standar #' . $this->quality_standard_id;
    }
}
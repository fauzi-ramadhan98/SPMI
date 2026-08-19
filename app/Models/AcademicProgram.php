<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicProgram extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'faculty',
        'degree_level',
        'accreditation',
        'head_name',
        'head_nidn',
        'is_active',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function evaluations()
    {
        return $this->morphMany(Evaluation::class, 'evaluable');
    }
}

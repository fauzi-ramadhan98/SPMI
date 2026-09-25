<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedReportAttachment extends Model
{
    protected $fillable = [
        'generated_report_id',
        'title',
        'source_label',
        'source_type',
        'source_id',
        'file_path',
        'disk',
        'sort_order',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(GeneratedReport::class, 'generated_report_id');
    }

    /**
     * Jenis berkas untuk tampilan: PDF digabungkan utuh, gambar disematkan
     * sebagai halaman penuh, selain itu ditampilkan nama ekstensinya.
     */
    public function getJenisAttribute(): string
    {
        $ext = strtolower(pathinfo((string) $this->file_path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'PDF',
            'jpg', 'jpeg', 'png', 'gif' => 'Gambar',
            '' => '—',
            default => strtoupper($ext),
        };
    }

    public function getBerkasLabelAttribute(): string
    {
        return $this->file_path ? basename($this->file_path) : '—';
    }
}

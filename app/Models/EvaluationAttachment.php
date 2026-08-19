<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationAttachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'title',
        'category',
        'file_path',
        'file_name',
        'link',
        'uploaded_by',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

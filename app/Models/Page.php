<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_description',
        'author_id',
        'is_published',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

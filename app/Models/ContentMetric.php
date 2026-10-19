<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'recorded_at',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'date',
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'shares_count' => 'integer',
        ];
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}

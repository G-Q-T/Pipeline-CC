<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'task_name',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'handle_name',
    ];

    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_TIKTOK = 'tiktok';
    public const PLATFORM_INSTAGRAM = 'instagram';
    public const PLATFORM_LINKEDIN = 'linkedin';

    public const ALLOWED_PLATFORMS = [
        self::PLATFORM_YOUTUBE,
        self::PLATFORM_TIKTOK,
        self::PLATFORM_INSTAGRAM,
        self::PLATFORM_LINKEDIN,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }
}

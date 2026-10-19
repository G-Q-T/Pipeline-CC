<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public const ROLE_CREATOR = 'creator';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_ADMIN = 'admin';

    public function isCreator(): bool
    {
        return $this->role === self::ROLE_CREATOR;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * The channels owned by this user.
     */
    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    /**
     * The contents created by this user.
     */
    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    /**
     * The contents assigned to this editor.
     */
    public function assignedContents()
    {
        return $this->belongsToMany(Content::class, 'content_assignments', 'editor_id', 'content_id')
            ->withPivot('assigned_by', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Assignments created by this user.
     */
    public function assignmentsCreated()
    {
        return $this->hasMany(ContentAssignment::class, 'assigned_by');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

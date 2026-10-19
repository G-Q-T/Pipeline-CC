<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel_id',
        'title',
        'description',
        'script_draft',
        'format_type',
        'status',
        'priority',
        'scheduled_date',
        'published_date',
        'published_url',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'published_date' => 'date',
        ];
    }

    public const FORMAT_SHORT_VIDEO = 'short_video';
    public const FORMAT_LONG_VIDEO = 'long_video';
    public const FORMAT_CAROUSEL = 'carousel';
    public const FORMAT_SINGLE_POST = 'single_post';

    public const ALLOWED_FORMATS = [
        self::FORMAT_SHORT_VIDEO,
        self::FORMAT_LONG_VIDEO,
        self::FORMAT_CAROUSEL,
        self::FORMAT_SINGLE_POST,
    ];

    public const STATUS_IDEA = 'idea';
    public const STATUS_SCRIPTING = 'scripting';
    public const STATUS_RECORDING = 'recording';
    public const STATUS_EDITING = 'editing';
    public const STATUS_READY = 'ready';
    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ORDER = [
        self::STATUS_IDEA => 0,
        self::STATUS_SCRIPTING => 1,
        self::STATUS_RECORDING => 2,
        self::STATUS_EDITING => 3,
        self::STATUS_READY => 4,
        self::STATUS_PUBLISHED => 5,
    ];

    public const ALLOWED_STATUSES = [
        self::STATUS_IDEA,
        self::STATUS_SCRIPTING,
        self::STATUS_RECORDING,
        self::STATUS_EDITING,
        self::STATUS_READY,
        self::STATUS_PUBLISHED,
    ];

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public const ALLOWED_PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
    ];

    /**
     * Get valid transitions from the current status.
     * PRD Section 11.2:
     * - Moving forward 1 step is allowed.
     * - Moving backward to previous production step is allowed.
     * - Once published, it cannot be transitioned back through normal workflow.
     */
    public function getAllowedTransitions(): array
    {
        if ($this->status === self::STATUS_PUBLISHED) {
            return [];
        }

        $currentIndex = self::STATUS_ORDER[$this->status] ?? 0;
        $allowed = [];

        // Forward 1 step
        foreach (self::STATUS_ORDER as $status => $index) {
            if ($index === $currentIndex + 1) {
                $allowed[] = $status;
            }
            // Backward steps (any previous production step can be returned to for revisions)
            if ($index < $currentIndex) {
                $allowed[] = $status;
            }
        }

        return $allowed;
    }

    /**
     * Determine if status can be changed to target status.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        if ($this->status === $targetStatus) {
            return true;
        }

        return in_array($targetStatus, $this->getAllowedTransitions(), true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function checklists()
    {
        return $this->hasMany(ContentChecklist::class);
    }

    public function metrics()
    {
        return $this->hasMany(ContentMetric::class)->orderBy('recorded_at', 'desc');
    }

    public function latestMetric()
    {
        return $this->hasOne(ContentMetric::class)->latestOfMany('recorded_at');
    }

    public function assignments()
    {
        return $this->hasMany(ContentAssignment::class);
    }

    public function editors()
    {
        return $this->belongsToMany(User::class, 'content_assignments', 'content_id', 'editor_id')
            ->withPivot('assigned_by', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Calculate checklist completion percentage.
     */
    public function getChecklistPercentageAttribute(): int
    {
        $total = $this->checklists->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->checklists->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }
}

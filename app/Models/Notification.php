<?php

namespace App\Models;

use App\Models\User;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'data',
        'read',
        'read_at',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $appends = ['link'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLinkAttribute(): string
    {
        if (($this->data === null) || !isset($this->data['type'])) {
            return '#';
        }

        switch ($this->data['type']) {
            case 'valuation_update':
                return route('filament.admin.resources.valuation-updates.view', $this->data['valuation_update_id']);
            default:
                return '#';
        }
    }
    public function markAsRead(): void
    {
        $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }


    // Static helper methods for creating notifications
    public static function createForUser(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        array $data = []
    ): self {
        return static::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
        ]);
    }

    public static function createForAllUsers(
        string $title,
        string $message,
        string $type = 'info',
        array $data = []
    ): void {
        User::chunk(100, function ($users) use ($title, $message, $type, $data) {
            foreach ($users as $user) {
                static::createForUser($user, $title, $message, $type, $data);
            }
        });
    }

    public static function createSuccess(
        User $user,
        string $title,
        string $message,
        array $data = []
    ): self {
        return static::createForUser($user, $title, $message, 'success', $data);
    }

    public static function createWarning(
        User $user,
        string $title,
        string $message,
        array $data = []
    ): self {
        return static::createForUser($user, $title, $message, 'warning', $data);
    }

    public static function createError(
        User $user,
        string $title,
        string $message,
        array $data = []
    ): self {
        return static::createForUser($user, $title, $message, 'error', $data);
    }

    public static function createInfo(
        User $user,
        string $title,
        string $message,
        array $data = []
    ): self {
        return static::createForUser($user, $title, $message, 'info', $data);
    }
}

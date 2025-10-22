<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([UserScope::class])]
class DashboardCache extends Model
{
    protected $table = 'dashboard_cache';

    protected $fillable = ['dashboard_widget_id', 'user_id', 'data', 'expires_at'];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];

    public function dashboardWidget(): BelongsTo
    {
        return $this->belongsTo(Widget::class, 'dashboard_widget_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

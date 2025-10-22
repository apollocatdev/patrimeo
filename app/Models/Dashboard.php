<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Observers\DashboardObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([UserScope::class])]
#[ObservedBy(DashboardObserver::class)]
class Dashboard extends Model
{
    //
    protected $fillable = ['navigation_title', 'navigation_icon', 'navigation_sort_order', 'n_columns', 'settings', 'active', 'default', 'user_id'];

    protected $casts = [
        'active' => 'boolean',
        'default' => 'boolean',
        'settings' => 'array',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function widgets(): BelongsToMany
    {
        return $this->belongsToMany(Widget::class, 'dashboard_widget')
            ->withPivot(['sort', 'column_span', 'size'])
            ->withTimestamps()
            ->orderBy('pivot_sort');
    }
}

<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Enums\Widgets\WidgetType;
use App\Observers\WidgetObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



#[ScopedBy([UserScope::class])]
#[ObservedBy([WidgetObserver::class])]
class Widget extends Model
{
    //
    protected $fillable = ['title', 'description', 'user_id', 'type', 'parameters', 'sort'];

    protected $casts = [
        'type' => WidgetType::class,
        'parameters' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dashboards(): BelongsToMany
    {
        return $this->belongsToMany(Dashboard::class);
    }


    // public function widgetStats(): HasMany
    // {
    //     return $this->hasMany(WidgetStat::class);
    // }


    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'filterables', 'filterable_id', 'filter_id')
            ->where('filterable_type', 'App\\Models\\Widget')
            ->using(WidgetFilter::class)
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use App\Data\WidgetFilterData;
use App\Models\Scopes\UserScope;
use App\Data\WidgetStatFormatterData;
use Spatie\LaravelData\DataCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([UserScope::class])]
class WidgetStat extends Model
{
    protected $fillable = ['title', 'description', 'icon', 'color', 'entity', 'operation', 'column', 'filters', 'format', 'track_variation_unit', 'user_id'];

    protected $casts = [
        'filters' => DataCollection::class.':'.WidgetFilterData::class,
        'format' => WidgetStatFormatterData::class . ':default',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

}

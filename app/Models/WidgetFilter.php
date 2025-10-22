<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetFilter extends MorphPivot
{
    protected $table = 'filterables';

    protected $fillable = [
        'filter_id',
        'filterable_id',
        'filterable_type',
    ];

    protected $morphType = 'filterable_type';

    public function widget()
    {
        return $this->belongsTo(Widget::class, 'filterable_id')
            ->where('filterable_type', Widget::class);
    }

    public function filter()
    {
        return $this->belongsTo(Filter::class);
    }
}

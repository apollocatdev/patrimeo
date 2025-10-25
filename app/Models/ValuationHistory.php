<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([UserScope::class])]
class ValuationHistory extends Model
{
    protected $fillable = ['date', 'value', 'value_main_currency', 'valuation_id', 'user_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function valuation(): BelongsTo
    {
        return $this->belongsTo(Valuation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

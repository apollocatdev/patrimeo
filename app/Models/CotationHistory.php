<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([UserScope::class])]
class CotationHistory extends Model
{
    protected $fillable = ['date', 'value', 'value_main_currency', 'cotation_id', 'user_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function cotation(): BelongsTo
    {
        return $this->belongsTo(Cotation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Enums\CryptoPoolUpdateMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ScopedBy([UserScope::class])]
class CryptoPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'liquidity',
        'apy',
        'utilization_rate',
        'url',
        'other_data',
        'last_update',
        'update_method',
        'update_data',
        'asset_id',
        'user_id',
    ];

    protected $casts = [
        'liquidity' => 'float',
        'apy' => 'float',
        'utilization_rate' => 'integer',
        'other_data' => 'array',
        'last_update' => 'datetime',
        'update_method' => CryptoPoolUpdateMethod::class,
        'update_data' => 'array',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'schedulable', 'schedulables');
    }
}

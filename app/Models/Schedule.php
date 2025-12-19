<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Data\Schedules\Actions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cron',
        'actions',
        'user_id',
    ];

    protected $casts = [
        'actions' => Actions::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function valuations(): MorphToMany
    {
        return $this->morphedByMany(Valuation::class, 'schedulable', 'schedulables');
    }

    public function assets(): MorphToMany
    {
        return $this->morphedByMany(Asset::class, 'schedulable', 'schedulables');
    }

    public function cryptoPools(): MorphToMany
    {
        return $this->morphedByMany(CryptoPool::class, 'schedulable', 'schedulables');
    }
}

<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class Envelop extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type_id', 'user_id'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EnvelopType::class);
    }
}

<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([UserScope::class])]
class Currency extends Model
{
    use HasFactory;
    protected $fillable = ['symbol', 'main', 'user_id'];

    protected $casts = [
        'main' => 'boolean',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get the default currency for the current user
     */
    public static function getDefault(): ?self
    {
        return self::where('main', true)->first();
    }
}

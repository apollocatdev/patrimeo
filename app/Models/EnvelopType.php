<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class EnvelopType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function envelops(): HasMany
    {
        return $this->hasMany(Envelop::class);
    }
}

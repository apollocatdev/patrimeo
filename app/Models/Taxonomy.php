<?php

namespace App\Models;

use App\Enums\TaxonomyTypes;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class Taxonomy extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'weighted', 'user_id'];

    protected $casts = [
        'type' => TaxonomyTypes::class,
        'weighted' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($taxonomy) {
            // Transactions taxonomies are automatically unweighted
            if ($taxonomy->type === TaxonomyTypes::TRANSACTIONS) {
                $taxonomy->weighted = false;
            }
        });
    }

    public function tags(): HasMany
    {
        return $this->hasMany(TaxonomyTag::class);
    }
}

<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([UserScope::class])]
class TaxonomyTag extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'taxonomy_id', 'user_id'];

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'tags_assets', 'tag_id', 'asset_id');
    }

    public function transfers(): BelongsToMany
    {
        return $this->belongsToMany(Transfer::class, 'transfer_taxonomy_tags', 'tag_id', 'transfer_id');
    }
}

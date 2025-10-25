<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([UserScope::class])]
class Transaction extends Model
{
    protected $fillable = ['type', 'source_id', 'source_quantity', 'destination_id', 'destination_quantity', 'date', 'user_id', 'comment'];

    protected $casts = [
        'date' => 'datetime',
        'type' => TransactionType::class,
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'source_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'destination_id');
    }

    // public function currency(): BelongsTo
    // {
    //     return $this->belongsTo(Currency::class);
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTag::class, 'transaction_taxonomy_tags', 'transaction_id', 'tag_id');
    }

    /**
     * Check if a duplicate transaction already exists
     */
    public function checkDuplicate(): bool
    {
        $query = static::where('source_id', $this->source_id)
            ->where('destination_id', $this->destination_id)
            ->where('source_quantity', $this->source_quantity)
            ->where('destination_quantity', $this->destination_quantity)
            ->where('date', $this->date);

        // If the transaction is already saved, exclude it from duplicate check
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }
}

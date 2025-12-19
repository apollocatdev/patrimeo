<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\TransactionType;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([UserScope::class])]
class Transaction extends Model
{
    protected $fillable = ['type', 'source_id', 'source_quantity', 'destination_id', 'destination_quantity', 'date', 'user_id', 'comment', 'reconciled'];

    protected $casts = [
        'date' => 'datetime',
        'type' => TransactionType::class,
        'reconciled' => 'boolean',
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

    public function getSimpleAccountNameAttribute(): string
    {
        return $this->type === TransactionType::Expense ? $this->source->name : $this->destination->name;
    }

    public function getSimpleAmountAttribute(): float
    {
        return $this->type === TransactionType::Expense ? -$this->source_quantity : $this->destination_quantity;
    }



    /**
     * Check if a duplicate transaction already exists
     */
    public function checkDuplicate(): bool
    {
        $query = static::where('type', $this->type)
            ->where('source_id', $this->source_id)
            ->where('destination_id', $this->destination_id)
            ->where('source_quantity', $this->source_quantity)
            ->where('destination_quantity', $this->destination_quantity)
            ->whereDate('date', $this->date->format('Y-m-d'));

        // Only exclude current record if it has an ID (is saved)
        if ($this->id) {
            $query->whereNot('id', $this->id);
        }

        return $query->exists();
    }

    public static function createTransaction(TransactionType $type, Carbon $date, ?Asset $source, ?Asset $destination, float $quantity, string $comment, bool $reconciled = false): self
    {
        $transaction = new self();
        $transaction->type = $type;
        $transaction->date = $date;
        $transaction->comment = $comment;
        $transaction->reconciled = $reconciled;
        if ($type === TransactionType::Expense) {
            if ($source === null) {
                throw new \Exception('Source asset is required for expense transactions');
            }
            $transaction->source_id = $source->id;
            $transaction->source_quantity = $quantity;
            $transaction->destination_id = null;
            $transaction->destination_quantity = null;
            $transaction->user_id = $source->user_id;
        } else {
            if ($destination === null) {
                throw new \Exception('Destination asset is required for income transactions');
            }
            $transaction->source_id = null;
            $transaction->source_quantity = null;
            $transaction->destination_id = $destination->id;
            $transaction->destination_quantity = $quantity;
            $transaction->user_id = $destination->user_id;
        }
        return $transaction;
    }
}

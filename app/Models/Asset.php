<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Observers\AssetObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Transaction;
use App\Enums\TransactionUpdateMethod;

#[ScopedBy([UserScope::class])]
#[ObservedBy([AssetObserver::class])]
class Asset extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'envelop_id', 'class_id', 'quantity', 'valuation_id', 'value', 'last_update', 'update_method', 'update_data', 'user_id'];

    protected $casts = [
        'last_update' => 'datetime',
        'update_method' => TransactionUpdateMethod::class,
        'update_data' => 'array',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTag::class, 'tags_assets', 'asset_id', 'tag_id');
    }

    public function envelop(): BelongsTo
    {
        return $this->belongsTo(Envelop::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(AssetClass::class, 'class_id');
    }

    public function valuation(): BelongsTo
    {
        return $this->belongsTo(Valuation::class);
    }

    public function computeQuantity(): void
    {
        // Get all transactions that occurred after the last_update
        $transactions = Transaction::where('date', '>', $this->last_update)
            ->orderBy('date')
            ->get();

        $newQuantity = $this->quantity;
        $lastTransactionDate = null;

        foreach ($transactions as $transaction) {
            // If this asset is the source, subtract the quantity
            if ($transaction->source_id === $this->id) {
                $newQuantity -= $transaction->source_quantity ?? 0;
            }

            // If this asset is the destination, add the quantity
            if ($transaction->destination_id === $this->id) {
                $newQuantity += $transaction->destination_quantity ?? 0;
            }

            // Track the last transaction date
            if (!$lastTransactionDate || $transaction->date > $lastTransactionDate) {
                $lastTransactionDate = $transaction->date;
            }
        }

        // Update the asset quantity and last_update
        if ($lastTransactionDate !== null) {
            $this->last_update = $lastTransactionDate;
        }
        $this->quantity = $newQuantity;
        $this->save();
    }

    /**
     * Get the transaction service class for this asset
     */
    public function getTransactionServiceClass(): ?string
    {
        if (!$this->update_method) {
            return null;
        }

        return $this->update_method->getServiceClass();
    }

    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'schedulable', 'schedulables');
    }

    public function getRateLimiterKeyAttribute(): string
    {
        if ($this->update_method === TransactionUpdateMethod::FINARY) {
            return 'finary';
        }
        if ($this->update_method === TransactionUpdateMethod::WOOB) {
            return 'woob';
        }
        if ($this->update_method === TransactionUpdateMethod::COMMAND_JSON) {
            return 'command_json';
        }
        if ($this->update_method === TransactionUpdateMethod::COMMAND_SIMPLE_BALANCE) {
            return 'command_simple_balance';
        }
        return 'none';
    }
}

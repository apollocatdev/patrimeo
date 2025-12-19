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

    public function computeQuantity(): int
    {
        $transactions = Transaction::where('reconciled', false)->get();

        $newQuantity = $this->quantity;

        foreach ($transactions as $transaction) {
            // If this asset is the source, subtract the quantity
            if ($transaction->source_id === $this->id) {
                $newQuantity -= $transaction->source_quantity ?? 0;
            }

            // If this asset is the destination, add the quantity
            if ($transaction->destination_id === $this->id) {
                $newQuantity += $transaction->destination_quantity ?? 0;
            }
        }

        // Update the asset quantity and last_update
        $this->last_update = now();
        $this->quantity = $newQuantity;
        $this->save();

        foreach ($transactions as $transaction) {
            $transaction->reconciled = true;
            $transaction->save();
        }
        return count($transactions);
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
}

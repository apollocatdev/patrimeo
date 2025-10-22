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
use App\Models\Transfer;
use App\Enums\TransferUpdateMethod;

#[ScopedBy([UserScope::class])]
#[ObservedBy([AssetObserver::class])]
class Asset extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'envelop_id', 'class_id', 'quantity', 'cotation_id', 'value', 'last_update', 'update_method', 'update_data', 'user_id'];

    protected $casts = [
        'last_update' => 'datetime',
        'update_method' => TransferUpdateMethod::class,
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

    public function cotation(): BelongsTo
    {
        return $this->belongsTo(Cotation::class);
    }

    public function computeQuantity(): void
    {
        // Get all transfers that occurred after the last_update
        $transfers = Transfer::where('date', '>', $this->last_update)
            ->orderBy('date')
            ->get();

        $newQuantity = $this->quantity;
        $lastTransferDate = null;

        foreach ($transfers as $transfer) {
            // If this asset is the source, subtract the quantity
            if ($transfer->source_id === $this->id) {
                $newQuantity -= $transfer->source_quantity ?? 0;
            }

            // If this asset is the destination, add the quantity
            if ($transfer->destination_id === $this->id) {
                $newQuantity += $transfer->destination_quantity ?? 0;
            }

            // Track the last transfer date
            if (!$lastTransferDate || $transfer->date > $lastTransferDate) {
                $lastTransferDate = $transfer->date;
            }
        }

        // Update the asset quantity and last_update
        if ($lastTransferDate !== null) {
            $this->last_update = $lastTransferDate;
        }
        $this->quantity = $newQuantity;
        $this->save();
    }

    /**
     * Get the transfer service class for this asset
     */
    public function getTransferServiceClass(): ?string
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
        if ($this->update_method === TransferUpdateMethod::FINARY) {
            return 'finary';
        }
        if ($this->update_method === TransferUpdateMethod::WOOB) {
            return 'woob';
        }
        if ($this->update_method === TransferUpdateMethod::COMMAND_JSON) {
            return 'command_json';
        }
        if ($this->update_method === TransferUpdateMethod::COMMAND_SIMPLE_BALANCE) {
            return 'command_simple_balance';
        }
        return 'none';
    }
}

<?php

namespace App\Models;

use App\Services\Valuations\ValuationCss;
use App\Services\Valuations\ValuationXPath;
use App\Services\Valuations\ValuationYahoo;
use App\Models\Scopes\UserScope;
use App\Enums\ValuationUpdateMethod;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Settings\ValuationUpdateSettings;
use App\Observers\ValuationObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ScopedBy([UserScope::class])]
#[ObservedBy([ValuationObserver::class])]
class Valuation extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'isin', 'currency_id', 'value', 'value_main_currency', 'last_update', 'update_method', 'update_data', 'user_id'];

    protected $casts = [
        'last_update' => 'datetime',
        'update_data' => 'array',
        'update_method' => ValuationUpdateMethod::class,
    ];

    #[Scope]
    protected function mainCurrencyPairs(Builder $query): void
    {
        $mainCurrency = Currency::where('main', true)->first();
        $currencies = Currency::all();
        $allPairs = [];
        foreach ($currencies as $currency) {
            if ($currency->symbol !== $mainCurrency->symbol) {
                $allPairs[] = $currency->symbol . $mainCurrency->symbol;
            }
        }
        $query->whereIn('name', $allPairs);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ValuationUpdate::class);
    }

    public function lastUpdate(): ?ValuationUpdate
    {
        return $this->updates()->orderBy('date', 'desc')->first();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ValuationHistory::class);
    }


    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'schedulable', 'schedulables');
    }

    // public function getUpdatePeriodicityTextAttribute(): string
    // {
    //     return SettingsValuationUpdate::getForUser($this->user_id)->getValuationPeriodicityText($this->name, $this->update_method->value);
    // }
}

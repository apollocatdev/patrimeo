<?php

namespace App\Models;

use App\Services\Cotations\CotationCss;
use App\Services\Cotations\CotationXPath;
use App\Services\Cotations\CotationYahoo;
use App\Models\Scopes\UserScope;
use App\Enums\CotationUpdateMethod;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Settings\CotationUpdateSettings;
use App\Observers\CotationObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ScopedBy([UserScope::class])]
#[ObservedBy([CotationObserver::class])]
class Cotation extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'isin', 'currency_id', 'value', 'value_main_currency', 'last_update', 'update_method', 'update_data', 'user_id'];

    protected $casts = [
        'last_update' => 'datetime',
        'update_data' => 'array',
        'update_method' => CotationUpdateMethod::class,
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

    public function getRateLimiterKeyAttribute(): string
    {
        if ($this->update_method === CotationUpdateMethod::YAHOO) {
            return 'yahoo';
        }
        if ($this->update_method === CotationUpdateMethod::XPATH) {
            return parse_url($this->update_data['url'], PHP_URL_HOST);
        }
        if ($this->update_method === CotationUpdateMethod::OPENAI) {
            return 'openai';
        }
        return 'none';
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
        return $this->hasMany(CotationUpdate::class);
    }

    public function lastUpdate(): ?CotationUpdate
    {
        return $this->updates()->orderBy('date', 'desc')->first();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CotationHistory::class);
    }


    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'schedulable', 'schedulables');
    }

    // public function getUpdatePeriodicityTextAttribute(): string
    // {
    //     return SettingsCotationUpdate::getForUser($this->user_id)->getCotationPeriodicityText($this->name, $this->update_method->value);
    // }
}

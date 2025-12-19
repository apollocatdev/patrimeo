<?php

namespace App\Helpers;

use Illuminate\Support\Number;
use App\Models\Currency as CurrencyModel;
use App\Settings\LocalizationSettings;

class Currency
{
    public static function sanitizeToFloat(string $value): float
    {
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d.]/', '', $value);
        return (float) $value;
    }

    public static function toCurrency(float $value): string
    {
        $mainCurrency = CurrencyModel::where('main', true)->first();
        $locale = LocalizationSettings::get()->numberFormat;
        return Number::currency($value, in: $mainCurrency->symbol, locale: $locale);
    }
}

<?php

namespace App\Enums\Filters;

use Filament\Support\Contracts\HasLabel;

enum FilterRuleAssetType: string implements HasLabel
{
    case ASSET_CLASS = 'class';
    case ENVELOP = 'envelop';
    case TAXONOMY = 'taxonomy';
    case UPDATE_METHOD = 'update_method';

    case VALUE = 'value';
    case QUANTITY = 'quantity';


    public function getLabel(): string
    {
        return match ($this) {
            self::ASSET_CLASS => __('Asset class'),
            self::ENVELOP => __('Asset envelop'),
            self::TAXONOMY => __('Asset taxonomy'),
            self::UPDATE_METHOD => __('Asset update method'),
            self::VALUE => __('Asset value'),
            self::QUANTITY => __('Asset quantity'),
        };
    }

    // public function getLabelColumns(): ?array
    // {
    //     return match ($this) {
    //         self::ASSET_CLASS => null,
    //         self::ENVELOP => null,
    //         self::TAXONOMY => null,
    //         self::UPDATE_METHOD => null,
    //     };
    // }

    public function isNumericRule(): bool
    {
        return in_array($this, [self::VALUE, self::QUANTITY]);
    }
}

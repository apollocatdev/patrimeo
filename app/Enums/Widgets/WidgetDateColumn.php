<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetDateColumn: string implements HasLabel
{
    case ASSET_MODIFIED_AT = 'modified_at';
    case VALUATION_LAST_UPDATE = 'last_update';

    public function getLabel(): string
    {
        return match ($this) {
            self::ASSET_MODIFIED_AT => __('Modified At'),
            self::VALUATION_LAST_UPDATE => __('Last Update'),
        };
    }

    public function getEntity(): string
    {
        return match ($this) {
            self::ASSET_MODIFIED_AT => 'assets',
            self::VALUATION_LAST_UPDATE => 'valuations',
        };
    }
}

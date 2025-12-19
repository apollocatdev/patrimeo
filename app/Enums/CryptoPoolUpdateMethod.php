<?php

namespace App\Enums;

use App\Services\Tools\Defillama;
use Filament\Support\Contracts\HasLabel;

enum CryptoPoolUpdateMethod: string implements HasLabel
{
    case DEFILLAMA = 'defillama';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DEFILLAMA => __('Defillama'),
        };
    }
    public function getServiceClass(): ?string
    {
        return match ($this) {
            self::DEFILLAMA => Defillama::class,
        };
    }
}

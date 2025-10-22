<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use App\Services\Importers\ImporterFinary;

enum Importers: string implements HasLabel
{
    case FINARY = 'finary';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FINARY => __('Finary'),
        };
    }

    public static function dropdown()
    {
        $dropdown = [];
        foreach (self::cases() as $case) {
            $dropdown[] = ['label' => $case->getLabel(), 'value' => $case->value];
        }
        return $dropdown;
    }

    public function getClass(): ?string
    {
        return match ($this) {
            self::FINARY => ImporterFinary::class,
        };
    }
}

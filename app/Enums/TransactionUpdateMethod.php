<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use App\Services\Transfers\TransfersCommandJson;
use App\Services\Transfers\TransfersCommandSimpleBalance;
use App\Services\Transfers\TransfersWoob;
use App\Services\Transfers\TransfersFinary;

enum TransferUpdateMethod: string implements HasLabel
{
    case MANUAL = 'manual';
    case FIXED = 'fixed';
    case COMMAND_JSON = 'command_json';
    case COMMAND_SIMPLE_BALANCE = 'command_simple_balance';
    case WOOB = 'woob';
    case FINARY = 'finary';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MANUAL => __('Manual'),
            self::FIXED => __('Fixed'),
            self::COMMAND_JSON => __('Command JSON'),
            self::COMMAND_SIMPLE_BALANCE => __('Command Simple Balance'),
            self::WOOB => __('Woob (Weboob)'),
            self::FINARY => __('Finary'),
        };
    }

    public function getServiceClass(): ?string
    {
        return match ($this) {
            self::COMMAND_JSON => TransfersCommandJson::class,
            self::COMMAND_SIMPLE_BALANCE => TransfersCommandSimpleBalance::class,
            self::WOOB => TransfersWoob::class,
            self::FINARY => TransfersFinary::class,
            self::FIXED, self::MANUAL => null,
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
}

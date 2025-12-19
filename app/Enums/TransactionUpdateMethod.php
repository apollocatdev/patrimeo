<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use App\Services\Transactions\TransactionsCommandJson;
use App\Services\Transactions\TransactionsCommandSimpleBalance;
use App\Services\Transactions\TransactionsWoob;
use App\Services\Transactions\TransactionsFinary;
use App\Services\Transactions\TransactionsLunchFlow;

enum TransactionUpdateMethod: string implements HasLabel
{
    case MANUAL = 'manual';
    case FIXED = 'fixed';
    case COMMAND_JSON = 'command_json';
    case COMMAND_SIMPLE_BALANCE = 'command_simple_balance';
    case WOOB = 'woob';
    case FINARY = 'finary';
    case LUNCHFLOW = 'lunchflow';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MANUAL => __('Manual'),
            self::FIXED => __('Fixed'),
            self::COMMAND_JSON => __('Command JSON'),
            self::COMMAND_SIMPLE_BALANCE => __('Command Simple Balance'),
            self::WOOB => __('Woob (Weboob)'),
            self::FINARY => __('Finary'),
            self::LUNCHFLOW => __('Lunch Flow'),
        };
    }

    public function getServiceClass(): ?string
    {
        return match ($this) {
            self::COMMAND_JSON => TransactionsCommandJson::class,
            self::COMMAND_SIMPLE_BALANCE => TransactionsCommandSimpleBalance::class,
            self::WOOB => TransactionsWoob::class,
            self::FINARY => TransactionsFinary::class,
            self::LUNCHFLOW => TransactionsLunchFlow::class,
            self::FIXED, self::MANUAL => null,
        };
    }

    public function getRateLimiterKey(): string
    {
        return match ($this) {
            self::FINARY => 'finary',
            self::WOOB => 'woob',
            self::LUNCHFLOW => 'lunchflow',
            self::COMMAND_JSON => 'command_json',
            self::COMMAND_SIMPLE_BALANCE => 'command_simple_balance',
            self::MANUAL, self::FIXED => 'none',
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

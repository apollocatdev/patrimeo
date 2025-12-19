<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ScheduleAction: string implements HasLabel
{
    case UPDATE = 'update';
    case NOTIFY_IN_APP = 'notify_in_app';
    case NOTIFY_BY_EMAIL = 'notify_by_email';
    case NOTIFY_BY_TELEGRAM = 'notify_by_telegram';

    public function getLabel(): string
    {
        return match ($this) {
            self::UPDATE => __('Update'),
            self::NOTIFY_IN_APP => __('Notify in App'),
            self::NOTIFY_BY_EMAIL => __('Notify by Email'),
            self::NOTIFY_BY_TELEGRAM => __('Notify by Telegram'),
        };
    }
}

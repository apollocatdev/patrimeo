<?php

namespace App\Helpers\Logs;

class LogCotations extends AbstractLog
{
    protected static function getChannelName(): string
    {
        return 'cotations';
    }

    protected static function getLogLevelSetting(): string
    {
        return 'cotation_log_level';
    }
}

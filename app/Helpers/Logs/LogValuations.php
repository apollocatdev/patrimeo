<?php

namespace App\Helpers\Logs;

class LogValuations extends AbstractLog
{
    protected static function getChannelName(): string
    {
        return 'valuations';
    }

    protected static function getLogLevelSetting(): string
    {
        return 'valuationLogLevel';
    }
}

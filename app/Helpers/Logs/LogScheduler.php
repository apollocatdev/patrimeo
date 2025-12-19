<?php

namespace App\Helpers\Logs;

class LogScheduler extends AbstractLog
{
    protected static function getChannelName(): string
    {
        return 'scheduler';
    }

    protected static function getLogLevelSetting(): string
    {
        return 'schedulerLogLevel';
    }
}

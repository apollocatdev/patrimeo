<?php

namespace App\Helpers\Logs;

class LogTools extends AbstractLog
{
    protected static function getChannelName(): string
    {
        return 'tools';
    }

    protected static function getLogLevelSetting(): string
    {
        return 'toolsLogLevel';
    }
}

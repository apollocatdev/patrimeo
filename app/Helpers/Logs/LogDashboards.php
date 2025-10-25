<?php

namespace App\Helpers\Logs;

class LogDashboards extends AbstractLog
{
    protected static function getChannelName(): string
    {
        return 'dashboards';
    }

    protected static function getLogLevelSetting(): string
    {
        return 'dashboardsLogLevel';
    }
}

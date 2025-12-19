<?php

namespace App\Helpers\Logs;

class LogTransactions extends AbstractLog
{
    protected static function getChannelName(): string
    {
        return 'transactions';
    }

    protected static function getLogLevelSetting(): string
    {
        return 'transactionsLogLevel';
    }
}

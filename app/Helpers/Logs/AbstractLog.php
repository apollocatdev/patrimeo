<?php

namespace App\Helpers\Logs;

use App\Settings\VariousSettings;
use Illuminate\Support\Facades\Log;

abstract class AbstractLog
{
    protected static ?int $currentUserId = null;

    abstract protected static function getChannelName(): string;

    abstract protected static function getLogLevelSetting(): string;

    /**
     * Set the current user ID for logging context
     */
    public static function setCurrentUserId(?int $userId): void
    {
        static::$currentUserId = $userId;
    }

    /**
     * Get the current user ID for logging context
     */
    public static function getCurrentUserId(): ?int
    {
        return static::$currentUserId;
    }

    private static function getLogLevel(): string
    {
        try {
            // Try to get settings for the current user context first
            if (static::$currentUserId !== null) {
                $settings = \ApollocatDev\FilamentSettings\Facades\FilamentSettings::getSettingForUser(VariousSettings::class, static::$currentUserId);
            } else {
                $settings = VariousSettings::get();
            }
        } catch (\Exception $e) {
            // If no user is authenticated (e.g., in queue jobs), use default settings
            $settings = VariousSettings::default();
        }

        $logLevelProperty = static::getLogLevelSetting();

        return $settings->{$logLevelProperty} ?? 'none';
    }

    public static function debug(string $message, array $context = []): void
    {
        if (static::shouldLog('debug')) {
            $context = static::addUserContext($context);
            Log::channel(static::getChannelName())->debug($message, $context);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        if (static::shouldLog('info')) {
            $context = static::addUserContext($context);
            Log::channel(static::getChannelName())->info($message, $context);
        }
    }

    public static function warning(string $message, array $context = []): void
    {
        if (static::shouldLog('warning')) {
            $context = static::addUserContext($context);
            Log::channel(static::getChannelName())->warning($message, $context);
        }
    }

    public static function error(string $message, array $context = []): void
    {
        if (static::shouldLog('error')) {
            $context = static::addUserContext($context);
            Log::channel(static::getChannelName())->error($message, $context);
        }
    }

    /**
     * Add user context to log context
     */
    private static function addUserContext(array $context): array
    {
        if (static::$currentUserId !== null) {
            $context['user_id'] = static::$currentUserId;
        }
        return $context;
    }

    private static function shouldLog(string $level): bool
    {
        $logLevel = static::getLogLevel();

        if ($logLevel === 'none') {
            return false;
        }

        if ($logLevel === 'debug') {
            return true;
        }

        if ($logLevel === 'info') {
            return in_array($level, ['info', 'warning', 'error']);
        }

        if ($logLevel === 'warning') {
            return in_array($level, ['warning', 'error']);
        }
        if ($logLevel === 'error') {
            return in_array($level, ['error']);
        }

        return false;
    }
}

<?php

// config for ApollocatDev/FilamentSettings
return [
    /*
    |--------------------------------------------------------------------------
    | Settings Table Name
    |--------------------------------------------------------------------------
    |
    | This is the name of the table that will store your settings.
    |
    */
    'table_name' => env('FILAMENT_SETTINGS_TABLE', 'filament_settings'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how long settings should be cached.
    |
    */
    'cache' => [
        'ttl' => env('FILAMENT_SETTINGS_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('FILAMENT_SETTINGS_CACHE_PREFIX', 'filament_settings'),
    ],


    /*
    |--------------------------------------------------------------------------
    | Filament Page Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Filament settings page appearance and behavior.
    |
    */
    'page' => [
        'navigation_label' => env('FILAMENT_SETTINGS_PAGE_LABEL', 'Settings'),
        'navigation_group' => env('FILAMENT_SETTINGS_PAGE_GROUP', null),
        'navigation_sort' => env('FILAMENT_SETTINGS_PAGE_SORT', 99),
        'navigation_icon' => env('FILAMENT_SETTINGS_PAGE_ICON', 'heroicon-o-cog-6-tooth'),
        'model_label' => env('FILAMENT_SETTINGS_MODEL_LABEL', 'Setting'),
        'model_label_plural' => env('FILAMENT_SETTINGS_MODEL_LABEL_PLURAL', 'Settings'),
    ],
];

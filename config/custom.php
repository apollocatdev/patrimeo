<?php

return [
    'version' => '0.2.2-alpha',
    'rate_limiters' => [
        'yahoo' => 3,
        'default' => 1,
    ],

    'default_settings' => [
        'localization' => [
            'number_format' => 'fr',
            'date_format' => 'fr',
            'date_format_separator' => '/'
        ],
        'theme' => [
            'chart_palette' => 'palette6',
        ],
        'security' => [
            'session_lifetime' => 120,
            'session_expire_on_close' => false,
        ]
    ],

    'chrome_path' => env('CHROME_PATH', '/usr/bin/chromium'),

];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 默认 MongoDB 连接
    |--------------------------------------------------------------------------
    */
    'default' => env('DB_CONNECTION_MONGODB', 'mongodb'),

    /*
    |--------------------------------------------------------------------------
    | MongoDB 连接配置
    |--------------------------------------------------------------------------
    */
    'connections' => [
        'mongodb' => [
            'driver' => 'mongodb',
            'dsn' => env('MONGODB_URI', 'mongodb://localhost:27017'),
            'database' => env('MONGODB_DATABASE', 'laravel'),
            'options' => [
                'authSource' => env('MONGODB_AUTH_SOURCE', 'admin'),
                'retryWrites' => true,
                'retryReads' => true,
                'readPreference' => 'primaryPreferred',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 系统配置
    |--------------------------------------------------------------------------
    */
    'system' => [
        // 慢查询阈值（毫秒）
        'slow_query_threshold' => 100,

        // 默认每页大小
        'default_per_page' => 15,

        // 最大每页大小
        'max_per_page' => 1000,

        // 日志记录
        'log_queries' => env('MONGODB_LOG_QUERIES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | 缓存配置
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('MONGODB_CACHE_ENABLED', true),
        'ttl' => 3600, // 缓存过期时间（秒）
        'prefix' => 'mongodb_',
    ],
];

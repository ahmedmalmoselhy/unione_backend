<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Priority Levels
    |--------------------------------------------------------------------------
    |
    | Define priority levels for different job types. Lower number = higher priority.
    |
    */

    'priority_levels' => [
        'critical' => 1,  // Password resets, payment processing
        'high'     => 2,  // Enrollment confirmations, grade notifications
        'normal'   => 3,  // Webhook deliveries, announcement emails
        'low'      => 4,  // Audit log cleanup, analytics aggregation
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Retry Configuration
    |--------------------------------------------------------------------------
    */

    'retry_config' => [
        'webhook_delivery' => [
            'max_attempts' => 10,
            'retry_seconds' => [60, 300, 900, 1800, 3600, 7200, 14400, 28800, 57600, 86400], // Exponential backoff
            'timeout' => 30,
        ],
        'email_notification' => [
            'max_attempts' => 5,
            'retry_seconds' => [60, 300, 900, 3600, 7200],
            'timeout' => 60,
        ],
        'import_export' => [
            'max_attempts' => 3,
            'retry_seconds' => [120, 600, 1800],
            'timeout' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Job Configuration
    |--------------------------------------------------------------------------
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],

];

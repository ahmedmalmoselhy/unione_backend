<?php

use Dedoc\Scramble\Scramble;

return [
    /*
    |--------------------------------------------------------------------------
    | Scramble Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration is for Scramble, a Laravel OpenAPI documentation generator.
    | It will generate interactive API documentation at /docs/api
    |
    */

    'middleware' => ['web'],
    
    'route' => [
        'api' => 'docs/api',
    ],

    'info' => [
        'title' => 'UniOne API Documentation',
        'description' => 'Comprehensive API documentation for the UniOne University Management Platform. This API provides endpoints for student enrollment, grade management, attendance tracking, announcements, and administrative functions.',
        'version' => '1.0.0',
    ],

    'servers' => [
        [
            'url' => env('APP_URL', 'http://localhost:8000'),
            'description' => 'Local Development Server',
        ],
    ],

    'auth_middleware' => 'auth:sanctum',

    'preferred_security_schemes' => ['sanctum'],

    'security_schemes' => [
        'sanctum' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearer_format' => 'JWT',
            'description' => 'Laravel Sanctum token authentication. Use the /api/auth/login endpoint to obtain a token.',
        ],
    ],
];

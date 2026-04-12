<?php

use App\Models\User;
use App\Services\CacheService;

beforeEach(function () {
    $this->user = createUser(['email' => 'test@unione.com']);
});

it('returns health status with all services', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'timestamp',
            'version',
            'environment',
            'services' => [
                'database' => ['status', 'driver', 'response_time_ms', 'message'],
                'cache' => ['status', 'driver', 'response_time_ms', 'message'],
                'storage' => ['status', 'disk', 'response_time_ms', 'message'],
                'queue' => ['status', 'driver', 'message'],
            ],
        ]);
});

it('shows healthy status when all services are operational', function () {
    $response = $this->getJson('/api/health');

    $response->assertJson([
        'status' => 'healthy',
        'services' => [
            'database' => ['status' => 'healthy'],
            'cache' => ['status' => 'healthy'],
            'storage' => ['status' => 'healthy'],
        ],
    ]);
});

it('includes environment and version information', function () {
    $response = $this->getJson('/api/health');

    $response->assertJson([
        'environment' => config('app.env'),
    ]);

    expect($response->json('version'))->not->toBeNull();
    expect($response->json('timestamp'))->not->toBeNull();
});

it('checks database connectivity and response time', function () {
    $response = $this->getJson('/api/health');

    $dbStatus = $response->json('services.database');

    expect($dbStatus['status'])->toBe('healthy');
    expect($dbStatus['driver'])->toBe(config('database.default'));
    expect($dbStatus['response_time_ms'])->toBeNumeric();
});

it('checks cache connectivity and response time', function () {
    $response = $this->getJson('/api/health');

    $cacheStatus = $response->json('services.cache');

    expect($cacheStatus['status'])->toBe('healthy');
    expect($cacheStatus['driver'])->toBe(config('cache.default'));
    expect($cacheStatus['response_time_ms'])->toBeNumeric();
});

it('checks storage connectivity', function () {
    $response = $this->getJson('/api/health');

    $storageStatus = $response->json('services.storage');

    expect($storageStatus['status'])->toBe('healthy');
    expect($storageStatus['disk'])->toBe('public');
    expect($storageStatus['response_time_ms'])->toBeNumeric();
});

it('checks queue system status', function () {
    $response = $this->getJson('/api/health');

    $queueStatus = $response->json('services.queue');

    expect($queueStatus['status'])->toBe('healthy');
    expect($queueStatus['driver'])->toBe(config('queue.default'));
});

it('is publicly accessible without authentication', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200);
});

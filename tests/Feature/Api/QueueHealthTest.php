<?php

use App\Models\User;
use App\Services\QueueMonitorService;

beforeEach(function () {
    $this->admin = createUser(['email' => 'admin@unione.com']);
    createRole('admin', $this->admin);
    
    $this->monitor = app(QueueMonitorService::class);
});

it('returns queue health statistics for admin', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/queue/health');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'queue_stats' => [
                'pending',
                'processing',
                'failed',
                'queues',
            ],
            'oldest_job_age_seconds',
            'timestamp',
        ]);
});

it('reports healthy status when no jobs', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/queue/health');

    $response->assertJson([
        'status' => 'healthy',
    ]);
});

it('returns failed jobs list', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/queue/failed');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'failed_jobs',
            'total',
        ]);
});

it('allows clearing old failed jobs', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson('/api/v1/admin/queue/failed/clear', [
            'older_than_hours' => 72,
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'deleted_count',
        ]);
});

it('restricts queue endpoints to admins only', function () {
    $student = createUser(['email' => 'student@unione.com']);
    createRole('student', $student);

    $response = $this->actingAs($student, 'sanctum')
        ->getJson('/api/v1/admin/queue/health');

    $response->assertStatus(403);
});

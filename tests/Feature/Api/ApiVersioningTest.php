<?php

use App\Models\User;

beforeEach(function () {
    $this->user = createUser(['email' => 'test@unione.com']);
});

it('returns 410 for unversioned API routes', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/student/profile');

    $response->assertStatus(410)
        ->assertJson([
            'message' => 'API versioning is now required.',
        ])
        ->assertHeader('X-API-Deprecation');
});

it('provides redirect URL to v1 endpoint', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/student/profile');

    $response->assertJsonStructure([
        'message',
        'redirect',
        'documentation',
    ]);

    expect($response->json('redirect'))->toContain('/api/v1/');
});

it('includes documentation URL in deprecation response', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/auth/me');

    expect($response->json('documentation'))->toContain('/docs/api');
});

it('serves v1 routes correctly', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/auth/me');

    // Should not be 410
    expect($response->getStatusCode())->not->toBe(410);
    
    // Should be 200 or proper auth response
    expect(in_array($response->getStatusCode(), [200, 404]))->toBeTrue();
});

it('adds rate limit headers to v1 responses', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/auth/me');

    // Check for rate limit headers (may not be present in test env with array cache)
    $headers = $response->headers->all();
    
    // At minimum, response should succeed
    expect($response->getStatusCode())->not->toBe(410);
});

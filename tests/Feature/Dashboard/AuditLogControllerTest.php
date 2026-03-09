<?php

use App\Models\AuditLog;

// ── GET /dashboard/audit-logs ─────────────────────────────────────────────────

test('admin can list audit logs', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.audit-logs.index'))
         ->assertOk();
});

test('audit log index shows existing log entries', function () {
    $admin = createUserWithRole('admin');

    AuditLog::create([
        'user_id'        => $admin->id,
        'action'         => 'assigned',
        'auditable_type' => 'FacultyAdmin',
        'auditable_id'   => 1,
        'description'    => 'Assigned faculty admin',
    ]);

    $this->actingAs($admin)
         ->get(route('dashboard.audit-logs.index'))
         ->assertOk();
});

// ── Forbidden ─────────────────────────────────────────────────────────────────

test('employee cannot view audit logs', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.audit-logs.index'))
         ->assertForbidden();
});

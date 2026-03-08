<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()
    ->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

// Load shared API test helpers
require_once __DIR__ . '/Feature/Api/helpers.php';

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Global Test Helpers
|--------------------------------------------------------------------------
*/

/**
 * Get or create a Role by name.
 */
function createRole(string $name): Role
{
    return Role::firstOrCreate(
        ['name' => $name],
        ['label' => ucfirst(str_replace('_', ' ', $name))]
    );
}

/**
 * Create a User via the factory.
 */
function createUser(array $attrs = []): User
{
    return User::factory()->create($attrs);
}

/**
 * Create a User and attach them to the given role.
 * $pivotAttrs may include faculty_id, department_id, etc.
 */
function createUserWithRole(string $roleName, array $userAttrs = [], array $pivotAttrs = []): User
{
    $user = createUser($userAttrs);
    $role = createRole($roleName);

    DB::table('role_user')->insert(array_merge([
        'user_id'    => $user->id,
        'role_id'    => $role->id,
        'granted_at' => now(),
    ], $pivotAttrs));

    return $user;
}

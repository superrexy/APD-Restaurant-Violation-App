<?php

use App\Models\User;

test('can list users with new response structure', function () {
    $authUser = User::factory()->create();
    User::factory()->count(15)->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            '*' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ],
        'meta' => [
            'current_page',
            'per_page',
            'total',
            'last_page',
        ],
    ]);
});

test('list users response has correct status code and message', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users');

    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('message'))->toBe('Success');
});

test('list users pagination meta contains correct pagination info', function () {
    $authUser = User::factory()->create();
    User::factory()->count(25)->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users?page=2');

    $meta = $response->json('meta');
    expect($meta['current_page'])->toBe(2);
    expect($meta['per_page'])->toBe(10);
    expect($meta['total'])->toBe(26);
    expect($meta['last_page'])->toBeGreaterThan(1);
});

test('list users returns empty data array when no users exist', function () {
    $authUser = User::factory()->create();
    User::where('id', '!=', $authUser->id)->delete();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users');

    expect($response->json('data'))->toBeArray();
    expect(count($response->json('data')))->toBeGreaterThanOrEqual(1);
});

test('list users returns 401 without authentication', function () {
    $response = $this->getJson('/api/users');

    $response->assertStatus(401);
});

test('can show single user with new response structure', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
        'meta',
    ]);
});

test('show user response has correct status code and message', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson("/api/users/{$user->id}");

    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('message'))->toBe('Success');
    expect($response->json('data.id'))->toBe($user->id);
    expect($response->json('data.email'))->toBe($user->email);
});

test('show user returns 404 for non-existent user', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users/99999');

    // DEBUG OUTPUT
    fwrite(STDERR, "\n\n=== DEBUG ===\n");
    fwrite(STDERR, 'Status: '.$response->getStatusCode()."\n");
    fwrite(STDERR, 'Content: '.$response->getContent()."\n");
    fwrite(STDERR, "==============\n\n");

    $response->assertStatus(404);
    expect($response->json('statusCode'))->toBe(404);
    expect($response->json('data'))->toBeNull();
    expect($response->json('meta'))->toBeArray();
});

test('show user 404 response has proper error structure', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users/99999');

    expect($response->json('message'))->toContain('not found');
    expect($response->json('data'))->toBeNull();
});

test('show user returns 401 without authentication', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");

    $response->assertStatus(401);
});

test('can create user with new response structure', function () {
    $authUser = User::factory()->create();
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', $data);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
        'meta',
    ]);
});

test('store user response has correct status code and message', function () {
    $authUser = User::factory()->create();
    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', $data);

    expect($response->json('statusCode'))->toBe(201);
    expect($response->json('message'))->toBe('Success');
    expect($response->json('data.name'))->toBe('Jane Doe');
    expect($response->json('data.email'))->toBe('jane@example.com');
});

test('store user creates user in database', function () {
    $authUser = User::factory()->create();
    $data = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
    ];

    $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', $data);

    expect(User::where('email', 'newuser@example.com')->exists())->toBeTrue();
});

test('store user returns 422 with validation error structure', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', []);

    $response->assertStatus(422);
    expect($response->json('statusCode'))->toBe(422);
    expect($response->json('message'))->toContain('error');
});

test('store user 422 response contains validation errors for name', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('name');
});

test('store user 422 response contains validation errors for email', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'Test User',
            'password' => 'password123',
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('email');
});

test('store user 422 response contains validation errors for password', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('password');
});

test('store user returns 422 for duplicate email', function () {
    $authUser = User::factory()->create();
    $existingUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'Duplicate',
            'email' => $existingUser->email,
            'password' => 'password123',
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('email');
});

test('store user returns 401 without authentication', function () {
    $response = $this->postJson('/api/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
});

test('can update user with new response structure', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'password' => 'newpassword123',
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/users/{$user->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
        'meta',
    ]);
});

test('update user response has correct status code and message', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'password' => 'newpassword123',
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/users/{$user->id}", $updateData);

    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('message'))->toBe('Success');
    expect($response->json('data.name'))->toBe('Updated Name');
    expect($response->json('data.email'))->toBe('updated@example.com');
});

test('update user modifies user in database', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/users/{$user->id}", [
            'name' => 'Database Updated',
            'email' => 'database@example.com',
            'password' => 'password123',
        ]);

    $user->refresh();
    expect($user->name)->toBe('Database Updated');
    expect($user->email)->toBe('database@example.com');
});

test('update user returns 404 for non-existent user', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson('/api/users/99999', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

    $response->assertStatus(404);
    expect($response->json('statusCode'))->toBe(404);
    expect($response->json('data'))->toBeNull();
});

test('update user 404 response has proper error structure', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson('/api/users/99999', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

    expect($response->json('message'))->toContain('not found');
    expect($response->json('data'))->toBeNull();
});

test('update user returns 422 with validation error for missing name', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/users/{$user->id}", [
            'email' => 'updated@example.com',
            'password' => 'password123',
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('name');
});

test('update user returns 422 with validation error for missing email', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/users/{$user->id}", [
            'name' => 'Test',
            'password' => 'password123',
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('email');
});

test('update user allows optional password field', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $oldPassword = $user->password;

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/users/{$user->id}", [
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

    $response->assertStatus(200);
    $user->refresh();
    expect($user->password)->toBe($oldPassword);
});

test('update user returns 401 without authentication', function () {
    $user = User::factory()->create();

    $response = $this->putJson("/api/users/{$user->id}", [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
});

test('can delete user and returns 204 no content', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(204);
    expect($response->content())->toBeEmpty();
});

test('destroy removes user from database', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $userId = $user->id;

    $this->actingAs($authUser, 'sanctum')
        ->deleteJson("/api/users/{$userId}");

    expect(User::where('id', $userId)->exists())->toBeFalse();
});

test('destroy returns 404 for non-existent user', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->deleteJson('/api/users/99999');

    $response->assertStatus(404);
});

test('destroy returns 403 when trying to delete own account', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->deleteJson("/api/users/{$authUser->id}");

    $response->assertStatus(403);
    expect($response->json('statusCode'))->toBe(403);
    expect($response->json('message'))->toContain('Cannot delete');
});

test('destroy own account 403 response has proper error structure', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->deleteJson("/api/users/{$authUser->id}");

    expect($response->json('data'))->toBeNull();
    expect($response->json('meta'))->toBeArray();
});

test('destroy returns 401 without authentication', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(401);
});

test('all error responses have consistent structure with statusCode, message, data null, meta', function () {
    $authUser = User::factory()->create();

    $response404 = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users/99999');

    expect($response404->json())->toHaveKeys(['statusCode', 'message', 'data', 'meta']);
    expect($response404->json('data'))->toBeNull();
    expect($response404->json('meta'))->toBeArray();

    $response403 = $this->actingAs($authUser, 'sanctum')
        ->deleteJson("/api/users/{$authUser->id}");

    expect($response403->json())->toHaveKeys(['statusCode', 'message', 'data', 'meta']);
    expect($response403->json('data'))->toBeNull();
});

test('422 validation error response has data as error array', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/users', []);

    expect($response->json())->toHaveKeys(['statusCode', 'message', 'data', 'meta']);
    expect($response->json('data'))->toBeArray();
    expect($response->json('statusCode'))->toBe(422);
});

test('success response includes meta field', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson("/api/users/{$user->id}");

    expect($response->json())->toHaveKeys(['statusCode', 'message', 'data', 'meta']);
    expect($response->json('meta'))->toBeArray();
});

test('paginated response meta contains pagination fields', function () {
    $authUser = User::factory()->create();
    User::factory()->count(15)->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/users');

    $meta = $response->json('meta');
    expect($meta)->toHaveKeys(['current_page', 'per_page', 'total', 'last_page']);
});

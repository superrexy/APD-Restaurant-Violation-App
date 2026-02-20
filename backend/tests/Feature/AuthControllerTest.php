<?php

use App\Models\User;

test('can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'token',
            'user' => [
                'id',
                'name',
                'email',
            ],
        ],
        'meta',
    ]);
    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('data.token'))->toBeTruthy();
});

test('login fails with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
    expect($response->json('statusCode'))->toBe(401);
    expect($response->json('data'))->toBeNull();
});

test('login fails with missing email', function () {
    $response = $this->postJson('/api/auth/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    expect($response->json('statusCode'))->toBe(422);
    expect($response->json('data'))->toHaveKey('email');
});

test('login fails with missing password', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422);
    expect($response->json('statusCode'))->toBe(422);
    expect($response->json('data'))->toHaveKey('password');
});

test('can logout with valid token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/auth/logout');

    $response->assertStatus(204);
    expect($response->content())->toBeEmpty();
});

test('logout fails without authentication', function () {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401);
    expect($response->json('statusCode'))->toBe(401);
});

test('logout revokes token and prevents further authenticated requests', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Verify token exists before logout
    expect($user->tokens()->count())->toBe(1);

    // Logout
    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/auth/logout');

    // Verify token was deleted from database
    $user->refresh();
    expect($user->tokens()->count())->toBe(0);
});

test('can refresh token with valid token', function () {
    $user = User::factory()->create();
    $oldToken = $user->createToken('api-token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$oldToken}"])
        ->postJson('/api/auth/refresh');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'token',
            'user' => [
                'id',
                'name',
                'email',
            ],
        ],
        'meta',
    ]);
    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('data.token'))->toBeTruthy();
});

test('refresh token fails without authentication', function () {
    $response = $this->postJson('/api/auth/refresh');

    $response->assertStatus(401);
    expect($response->json('statusCode'))->toBe(401);
});

test('refresh token returns new token different from old token', function () {
    $user = User::factory()->create();
    $oldToken = $user->createToken('api-token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$oldToken}"])
        ->postJson('/api/auth/refresh');

    $response->assertStatus(200);
    $newToken = $response->json('data.token');
    expect($newToken)->not->toBe($oldToken);
});

test('refresh token deletes old token after refresh', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Verify token works before refresh
    $beforeRefresh = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');
    expect($beforeRefresh->status())->not->toBe(401);

    // Get token count before refresh
    $tokenCountBefore = $user->tokens()->count();
    expect($tokenCountBefore)->toBe(1);

    // Refresh token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/auth/refresh');

    // Verify we got a new token
    $response->assertStatus(200);
    $newToken = $response->json('data.token');
    expect($newToken)->not->toBe($token);

    // Verify token count after refresh (should still be 1 - old deleted, new created)
    $user->refresh();
    $tokenCountAfter = $user->tokens()->count();
    expect($tokenCountAfter)->toBe(1);
});

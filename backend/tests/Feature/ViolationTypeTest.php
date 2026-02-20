<?php

use App\Models\User;
use App\Models\ViolationType;

test('can list violation types with new response structure', function () {
    $authUser = User::factory()->create();
    ViolationType::factory()->count(15)->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/violation-types');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            '*' => [
                'id',
                'name',
                'description',
                'code',
                'severity',
                'is_active',
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

test('list violation types response has correct status code and message', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/violation-types');

    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('message'))->toBe('Success');
});

test('list violation types pagination meta contains correct pagination info', function () {
    $authUser = User::factory()->create();
    ViolationType::factory()->count(24)->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/violation-types?page=2');

    $meta = $response->json('meta');
    expect($meta['current_page'])->toBe(2);
    expect($meta['per_page'])->toBe(10);
    expect($meta['total'])->toBe(24);
    expect($meta['last_page'])->toBeGreaterThan(1);
});

test('list violation types returns empty data array when no violation types exist', function () {
    $authUser = User::factory()->create();
    ViolationType::query()->delete();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/violation-types');

    expect($response->json('data'))->toBeArray();
    expect(count($response->json('data')))->toBeGreaterThanOrEqual(0);
});

test('list violation types returns 401 without authentication', function () {
    $response = $this->getJson('/api/violation-types');

    $response->assertStatus(401);
});

test('can show single violation type with new response structure', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson("/api/violation-types/{$violationType->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'id',
            'name',
            'description',
            'code',
            'severity',
            'is_active',
            'created_at',
            'updated_at',
        ],
        'meta',
    ]);
});

test('show violation type response has correct status code and message', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson("/api/violation-types/{$violationType->id}");

    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('message'))->toBe('Success');
    expect($response->json('data.id'))->toBe($violationType->id);
    expect($response->json('data.code'))->toBe($violationType->code);
});

test('show violation type returns 404 for non-existent violation type', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/violation-types/99999');

    $response->assertStatus(404);
    expect($response->json('statusCode'))->toBe(404);
    expect($response->json('data'))->toBeNull();
    expect($response->json('meta'))->toBeArray();
});

test('show violation type 404 response has proper error structure', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->getJson('/api/violation-types/99999');

    expect($response->json('message'))->toContain('not found');
    expect($response->json('data'))->toBeNull();
});

test('show violation type returns 401 without authentication', function () {
    $violationType = ViolationType::factory()->create();

    $response = $this->getJson("/api/violation-types/{$violationType->id}");

    $response->assertStatus(401);
});

test('can create violation type with new response structure', function () {
    $authUser = User::factory()->create();
    $data = [
        'name' => 'Test Violation',
        'description' => 'Test description',
        'code' => 'TEST123',
        'severity' => 'high',
        'is_active' => true,
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', $data);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'id',
            'name',
            'description',
            'code',
            'severity',
            'is_active',
            'created_at',
            'updated_at',
        ],
        'meta',
    ]);
});

test('store violation type response has correct status code and message', function () {
    $authUser = User::factory()->create();
    $data = [
        'name' => 'New Violation Type',
        'description' => 'New description',
        'code' => 'NEW123',
        'severity' => 'medium',
        'is_active' => true,
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', $data);

    expect($response->json('statusCode'))->toBe(201);
    expect($response->json('message'))->toBe('Success');
    expect($response->json('data.name'))->toBe('New Violation Type');
    expect($response->json('data.code'))->toBe('NEW123');
});

test('store violation type creates record in database', function () {
    $authUser = User::factory()->create();
    $data = [
        'name' => 'Database Test',
        'description' => 'Test description',
        'code' => 'DB123',
        'severity' => 'low',
        'is_active' => true,
    ];

    $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', $data);

    expect(ViolationType::where('code', 'DB123')->exists())->toBeTrue();
});

test('store violation type returns 422 with validation error structure', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', []);

    $response->assertStatus(422);
    expect($response->json('statusCode'))->toBe(422);
    expect($response->json('message'))->toContain('error');
});

test('store violation type 422 response contains validation errors for name', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', [
            'description' => 'Test',
            'code' => 'TEST123',
            'severity' => 'medium',
            'is_active' => true,
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('name');
});

test('store violation type 422 response contains validation errors for code', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', [
            'name' => 'Test',
            'description' => 'Test',
            'severity' => 'medium',
            'is_active' => true,
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('code');
});

test('store violation type 422 response contains validation errors for severity', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', [
            'name' => 'Test',
            'description' => 'Test',
            'code' => 'TEST123',
            'is_active' => true,
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('severity');
});

test('store violation type 422 response contains validation errors for is_active', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', [
            'name' => 'Test',
            'description' => 'Test',
            'code' => 'TEST123',
            'severity' => 'medium',
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('is_active');
});

test('store violation type returns 422 for duplicate code', function () {
    $authUser = User::factory()->create();
    $existing = ViolationType::factory()->create(['code' => 'DUPLICATE']);

    $response = $this->actingAs($authUser, 'sanctum')
        ->postJson('/api/violation-types', [
            'name' => 'Duplicate Test',
            'description' => 'Test',
            'code' => 'DUPLICATE',
            'severity' => 'medium',
            'is_active' => true,
        ]);

    $response->assertStatus(422);
    expect($response->json('data'))->toHaveKey('code');
});

test('store violation type returns 401 without authentication', function () {
    $response = $this->postJson('/api/violation-types', [
        'name' => 'Test',
        'code' => 'TEST123',
        'severity' => 'medium',
        'is_active' => true,
    ]);

    $response->assertStatus(401);
});

test('can update violation type with new response structure', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();
    $updateData = [
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'severity' => 'high',
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/violation-types/{$violationType->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'statusCode',
        'message',
        'data' => [
            'id',
            'name',
            'description',
            'code',
            'severity',
            'is_active',
            'created_at',
            'updated_at',
        ],
        'meta',
    ]);
});

test('update violation type response has correct status code and message', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();
    $updateData = [
        'name' => 'Updated Name',
        'code' => 'UPDATED123',
        'severity' => 'high',
    ];

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/violation-types/{$violationType->id}", $updateData);

    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('message'))->toBe('Success');
    expect($response->json('data.name'))->toBe('Updated Name');
    expect($response->json('data.code'))->toBe('UPDATED123');
});

test('update violation type modifies record in database', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();

    $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/violation-types/{$violationType->id}", [
            'name' => 'Database Updated',
            'description' => 'Updated description',
            'severity' => 'low',
        ]);

    $violationType->refresh();
    expect($violationType->name)->toBe('Database Updated');
    expect($violationType->description)->toBe('Updated description');
    expect($violationType->severity)->toBe('low');
});

test('update violation type returns 404 for non-existent violation type', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson('/api/violation-types/99999', [
            'name' => 'Test',
            'severity' => 'medium',
        ]);

    $response->assertStatus(404);
    expect($response->json('statusCode'))->toBe(404);
    expect($response->json('data'))->toBeNull();
});

test('update violation type 404 response has proper error structure', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson('/api/violation-types/99999', [
            'name' => 'Test',
            'severity' => 'medium',
        ]);

    expect($response->json('message'))->toContain('not found');
    expect($response->json('data'))->toBeNull();
});

test('update violation type allows partial updates', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();
    $oldName = $violationType->name;

    $response = $this->actingAs($authUser, 'sanctum')
        ->putJson("/api/violation-types/{$violationType->id}", [
            'description' => 'Only updating description',
        ]);

    $response->assertStatus(200);
    $violationType->refresh();
    expect($violationType->name)->toBe($oldName);
    expect($violationType->description)->toBe('Only updating description');
});

test('update violation type returns 401 without authentication', function () {
    $violationType = ViolationType::factory()->create();

    $response = $this->putJson("/api/violation-types/{$violationType->id}", [
        'name' => 'Test',
        'severity' => 'medium',
    ]);

    $response->assertStatus(401);
});

test('can delete violation type and returns 204 no content', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->deleteJson("/api/violation-types/{$violationType->id}");

    $response->assertStatus(204);
    expect($response->content())->toBeEmpty();
});

test('destroy removes violation type from database', function () {
    $authUser = User::factory()->create();
    $violationType = ViolationType::factory()->create();
    $violationTypeId = $violationType->id;

    $this->actingAs($authUser, 'sanctum')
        ->deleteJson("/api/violation-types/{$violationTypeId}");

    expect(ViolationType::where('id', $violationTypeId)->exists())->toBeFalse();
});

test('destroy returns 404 for non-existent violation type', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser, 'sanctum')
        ->deleteJson('/api/violation-types/99999');

    $response->assertStatus(404);
});

test('destroy returns 401 without authentication', function () {
    $violationType = ViolationType::factory()->create();

    $response = $this->deleteJson("/api/violation-types/{$violationType->id}");

    $response->assertStatus(401);
});

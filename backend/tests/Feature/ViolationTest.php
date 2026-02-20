<?php

use App\Models\Camera;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationDetail;
use App\Models\ViolationType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

afterEach(function () {
    Storage::fake('public');
});

test('can list violations', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    Violation::factory()->for($camera)->count(15)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/violations');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'camera_id',
                    'image_path',
                    'status',
                    'notes',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta',
        ]);
});

test('cannot list violations when unauthorized', function () {
    $response = $this->getJson('/api/violations');

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('can list empty violations', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/violations');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data',
            'meta',
        ]);
});

test('can show violation', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    ViolationDetail::factory()
        ->for($violation)
        ->for($violationType)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/violations/{$violation->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data' => [
                'id',
                'camera_id',
                'image_path',
                'status',
                'notes',
                'created_at',
                'updated_at',
                'camera' => [
                    'id',
                    'name',
                    'code',
                    'location',
                ],
                'violation_details' => [
                    '*' => [
                        'id',
                        'violation_id',
                        'violation_type_id',
                        'confidence_score',
                        'additional_info',
                        'status',
                    ],
                ],
            ],
        ]);
});

test('cannot show violation when unauthorized', function () {
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();

    $response = $this->getJson("/api/violations/{$violation->id}");

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('can create violation with image and violation details', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violationType = ViolationType::factory()->create();

    $image = UploadedFile::fake()->image('violation.jpg', 800, 600)->size(500);

    $data = [
        'image' => $image,
        'camera_code' => $camera->code,
        'notes' => 'Test violation notes',
        'violation_details' => [
            [
                'violation_code' => $violationType->code,
                'confidence_score' => 0.85,
                'additional_info' => 'Detail info',
            ],
        ],
    ];

    $response = $this->withHeaders(['X-API-KEY' => 'test-api-key'])
        ->postJson('/api/violations', $data);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data' => [
                'id',
                'camera_id',
                'image_path',
                'status',
                'notes',
            ],
        ]);

    $this->assertDatabaseHas('violations', [
        'camera_id' => $camera->id,
        'status' => 'pending',
        'notes' => 'Test violation notes',
    ]);

    $this->assertDatabaseHas('violation_details', [
        'violation_type_id' => $violationType->id,
        'confidence_score' => 0.85,
        'status' => 'unverified',
    ]);

    Storage::disk('public')->assertExists('violations/'.$response->json('data.image_path'));
});

test('cannot create violation when api key is missing', function () {
    $camera = Camera::factory()->create();
    $violationType = ViolationType::factory()->create();

    $data = [
        'image' => UploadedFile::fake()->image('test.jpg', 1024),
        'camera_code' => $camera->code,
        'notes' => 'Test violation',
        'violation_details' => [
            [
                'violation_code' => $violationType->code,
                'confidence_score' => 0.95,
                'additional_info' => 'High confidence',
            ],
        ],
    ];

    $response = $this->postJson('/api/violations', $data);

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthorized');
});

test('cannot create violation with invalid api key', function () {
    $camera = Camera::factory()->create();
    $violationType = ViolationType::factory()->create();

    $data = [
        'image' => UploadedFile::fake()->image('test.jpg', 1024),
        'camera_code' => $camera->code,
        'notes' => 'Test violation',
        'violation_details' => [
            [
                'violation_code' => $violationType->code,
                'confidence_score' => 0.95,
                'additional_info' => 'High confidence',
            ],
        ],
    ];

    $response = $this->withHeaders(['X-API-KEY' => 'invalid-key'])
        ->postJson('/api/violations', $data);

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthorized');
});

test('can update violation status', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create(['status' => 'pending']);

    $data = [
        'status' => 'reviewed',
        'notes' => 'Updated notes',
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/violations/{$violation->id}/status", $data);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data' => [
                'id',
                'status',
                'notes',
            ],
        ]);

    $this->assertDatabaseHas('violations', Violation::factory()->make([
        'id' => $violation->id,
        'status' => 'reviewed',
        'notes' => 'Updated notes',
    ])->toArray());
});

test('can delete violation', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/violations/{$violation->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('violations', [
        'id' => $violation->id,
    ]);
});

test('cannot delete violation when unauthorized', function () {
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();

    $response = $this->deleteJson("/api/violations/{$violation->id}");

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('violation status transitions correctly', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create(['status' => 'pending']);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/violations/{$violation->id}/status", ['status' => 'reviewed']);

    $this->assertDatabaseHas('violations', [
        'id' => $violation->id,
        'status' => 'reviewed',
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/violations/{$violation->id}/status", ['status' => 'resolved']);

    $this->assertDatabaseHas('violations', [
        'id' => $violation->id,
        'status' => 'resolved',
    ]);
});

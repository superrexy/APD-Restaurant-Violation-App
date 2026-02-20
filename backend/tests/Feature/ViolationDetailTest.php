<?php

use App\Models\Camera;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationDetail;
use App\Models\ViolationType;

test('can list violation details', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    ViolationDetail::factory()
        ->count(3)
        ->for($violation)
        ->for($violationType)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/violations/{$violation->id}/details");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'violation_id',
                    'violation_type_id',
                    'confidence_score',
                    'additional_info',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('cannot list violation details when unauthorized', function () {
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();

    $response = $this->getJson("/api/violations/{$violation->id}/details");

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('can show violation detail', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation)
        ->for($violationType)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/violation-details/{$detail->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data' => [
                'id',
                'violation_id',
                'violation_type_id',
                'confidence_score',
                'additional_info',
                'status',
                'violation' => [
                    'id',
                    'status',
                    'image_path',
                ],
                'violation_type' => [
                    'id',
                    'name',
                    'code',
                    'severity',
                ],
            ],
        ]);
});

test('cannot show violation detail when unauthorized', function () {
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation)
        ->for($violationType)
        ->create();

    $response = $this->getJson("/api/violation-details/{$detail->id}");

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('can update violation detail status', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation)
        ->for($violationType)
        ->create(['status' => 'unverified']);

    $data = [
        'status' => 'confirmed',
        'additional_info' => 'Updated detail info',
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/violation-details/{$detail->id}/status", $data);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'message',
            'data' => [
                'id',
                'status',
                'additional_info',
            ],
        ]);

    $this->assertDatabaseHas('violation_details', [
        'id' => $detail->id,
        'status' => 'confirmed',
        'additional_info' => 'Updated detail info',
    ]);
});

test('can delete violation detail', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation)
        ->for($violationType)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/violation-details/{$detail->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('violation_details', [
        'id' => $detail->id,
    ]);
});

test('cannot delete violation detail when unauthorized', function () {
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->create();
    $violationType = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation)
        ->for($violationType)
        ->create();

    $response = $this->deleteJson("/api/violation-details/{$detail->id}");

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('violation detail status transitions correctly', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation)
        ->for($violationType)
        ->create(['status' => 'unverified']);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/violation-details/{$detail->id}/status", ['status' => 'confirmed']);

    $this->assertDatabaseHas('violation_details', [
        'id' => $detail->id,
        'status' => 'confirmed',
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/violation-details/{$detail->id}/status", ['status' => 'dismissed']);

    $this->assertDatabaseHas('violation_details', [
        'id' => $detail->id,
        'status' => 'dismissed',
    ]);
});

test('violation detail belongs to correct violation', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation1 = Violation::factory()->for($camera)->create();
    $violation2 = Violation::factory()->for($camera)->create();
    $violationType = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation1)
        ->for($violationType)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/violation-details/{$detail->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.violation.id', $violation1->id)
        ->assertJsonPath('data.violation_id', $violation1->id);
});

test('violation detail belongs to correct violation type', function () {
    $user = User::factory()->create();
    $camera = Camera::factory()->create();
    $violation = Violation::factory()->for($camera)->create();
    $violationType1 = ViolationType::factory()->create();
    $violationType2 = ViolationType::factory()->create();
    $detail = ViolationDetail::factory()
        ->for($violation)
        ->for($violationType1)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/violation-details/{$detail->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.violation_type.id', $violationType1->id)
        ->assertJsonPath('data.violation_type_id', $violationType1->id);
});

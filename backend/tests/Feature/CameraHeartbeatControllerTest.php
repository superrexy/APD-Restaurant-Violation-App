<?php

use App\Http\Requests\CameraHeartbeatRequest;
use App\Models\Camera;

test('store with valid camera_code updates connected_at', function () {
    $camera = Camera::factory()->create([
        'code' => 'CAM001',
        'status' => 'inactive',
        'connected_at' => now()->subHours(1),
    ]);

    $response = $this->postJson('/api/camera-heartbeat', [
        'camera_code' => 'CAM001',
        'status' => 'active',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('statusCode', 200)
        ->assertJsonPath('message', 'Heartbeat received');

    $camera->refresh();
    expect($camera->connected_at)->toBeGreaterThan(now()->subSeconds(5));
});

test('store with invalid camera_code returns 422', function () {
    $response = $this->postJson('/api/camera-heartbeat', [
        'camera_code' => 'INVALID',
        'status' => 'active',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('statusCode', 422);
});

test('store updates status when provided', function () {
    $camera = Camera::factory()->create([
        'code' => 'CAM002',
        'status' => 'inactive',
    ]);

    $response = $this->postJson('/api/camera-heartbeat', [
        'camera_code' => 'CAM002',
        'status' => 'active',
    ]);

    $response->assertStatus(200);

    $camera->refresh();
    expect($camera->status)->toBe('active');
});

test('store requires camera_code', function () {
    $response = $this->postJson('/api/camera-heartbeat', [
        'status' => 'active',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('statusCode', 422)
        ->assertJsonStructure(['data']);
});

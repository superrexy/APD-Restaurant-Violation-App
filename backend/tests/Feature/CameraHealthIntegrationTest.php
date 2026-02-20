<?php

use App\Events\CameraStatusUpdated;
use App\Models\Camera;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

test('camera becomes active when yolo service health check succeeds', function () {
    Event::fake();

    $camera = Camera::factory()->create([
        'code' => 'CAM001',
        'status' => 'inactive',
        'yolo_detection_status' => false,
        'yolo_service_url' => 'http://localhost:8081',
        'connected_at' => null,
        'disconnected_at' => now()->subHour(),
    ]);

    Http::fake([
        'http://localhost:8081/health' => Http::response([
            'status' => 'ok',
            'active_clients' => 1,
        ], 200),
    ]);

    $this->artisan('camera:health-check')
        ->assertExitCode(0);

    $camera->refresh();

    expect($camera->status)->toBe('active');
    expect($camera->yolo_detection_status)->toBe(true);
    expect($camera->connected_at)->not->toBeNull();
    expect($camera->disconnected_at)->toBeNull();

    Event::assertDispatched(CameraStatusUpdated::class, function ($event) use ($camera) {
        return $event->camera_code === $camera->code
            && $event->status === 'active'
            && $event->connected_at !== null
            && $event->disconnected_at === null;
    });
});

test('camera becomes inactive when yolo service health check fails', function () {
    Event::fake();

    $camera = Camera::factory()->create([
        'code' => 'CAM002',
        'status' => 'active',
        'yolo_detection_status' => true,
        'yolo_service_url' => 'http://localhost:8082',
        'connected_at' => now()->subHour(),
        'disconnected_at' => null,
    ]);

    Http::fake([
        'http://localhost:8082/health' => Http::response([], 503),
    ]);

    $this->artisan('camera:health-check')
        ->assertExitCode(0);

    $camera->refresh();

    expect($camera->status)->toBe('inactive');
    expect($camera->yolo_detection_status)->toBe(false);
    expect($camera->connected_at)->toBeNull();
    expect($camera->disconnected_at)->not->toBeNull();

    Event::assertDispatched(CameraStatusUpdated::class, function ($event) use ($camera) {
        return $event->camera_code === $camera->code
            && $event->status === 'inactive'
            && $event->connected_at === null
            && $event->disconnected_at !== null;
    });
});

test('camera becomes inactive when yolo service times out', function () {
    Event::fake();

    $camera = Camera::factory()->create([
        'code' => 'CAM003',
        'status' => 'active',
        'yolo_detection_status' => true,
        'yolo_service_url' => 'http://localhost:8083',
        'connected_at' => now()->subHour(),
        'disconnected_at' => null,
    ]);

    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
    });

    $this->artisan('camera:health-check')
        ->assertExitCode(0);

    $camera->refresh();

    expect($camera->status)->toBe('inactive');
    expect($camera->yolo_detection_status)->toBe(false);
    expect($camera->connected_at)->toBeNull();
    expect($camera->disconnected_at)->not->toBeNull();

    Event::assertDispatched(CameraStatusUpdated::class, function ($event) use ($camera) {
        return $event->camera_code === $camera->code
            && $event->status === 'inactive';
    });
});

test('camera with same active status does not dispatch event', function () {
    Event::fake();

    $camera = Camera::factory()->create([
        'code' => 'CAM004',
        'status' => 'active',
        'yolo_detection_status' => true,
        'yolo_service_url' => 'http://localhost:8084',
        'connected_at' => now()->subHour(),
        'disconnected_at' => null,
    ]);

    Http::fake([
        'http://localhost:8084/health' => Http::response(['status' => 'ok'], 200),
    ]);

    $this->artisan('camera:health-check')
        ->assertExitCode(0);

    Event::assertNotDispatched(CameraStatusUpdated::class);
});

test('camera with same inactive status does not dispatch event', function () {
    Event::fake();

    $camera = Camera::factory()->create([
        'code' => 'CAM005',
        'status' => 'inactive',
        'yolo_detection_status' => false,
        'yolo_service_url' => 'http://localhost:8085',
        'connected_at' => null,
        'disconnected_at' => now()->subHour(),
    ]);

    Http::fake([
        'http://localhost:8085/health' => Http::response([], 503),
    ]);

    $this->artisan('camera:health-check')
        ->assertExitCode(0);

    Event::assertNotDispatched(CameraStatusUpdated::class);
});

<?php

use App\Models\Camera;

test('camera model accepts yolo_detection_status via mass assignment', function () {
    $camera = Camera::factory()->create([
        'yolo_detection_status' => true,
        'yolo_service_url' => 'http://test',
    ]);

    expect($camera->yolo_detection_status)->toBeTrue();
});

test('camera model casts yolo_detection_status to boolean', function () {
    $camera = Camera::factory()->create([
        'yolo_detection_status' => 1,
    ]);

    expect($camera->yolo_detection_status)->toBeBool()
        ->and($camera->yolo_detection_status)->toBeTrue();
});

test('camera model accepts yolo_service_url via mass assignment', function () {
    $camera = Camera::factory()->create([
        'yolo_service_url' => 'http://yolo:8081',
    ]);

    expect($camera->yolo_service_url)->toBe('http://yolo:8081');
});

test('camera model can have null yolo_service_url', function () {
    $camera = Camera::factory()->create([
        'yolo_service_url' => null,
    ]);

    expect($camera->yolo_service_url)->toBeNull();
});

test('camera model default yolo_detection_status is false', function () {
    $camera = Camera::factory()->create();

    expect($camera->yolo_detection_status)->toBeFalse();
});

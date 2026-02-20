<?php

use Illuminate\Support\Facades\Schema;

test('yolo_detection_status column exists in cameras table', function () {
    expect(Schema::hasColumn('cameras', 'yolo_detection_status'))->toBeTrue();
});

test('yolo_service_url column exists in cameras table', function () {
    expect(Schema::hasColumn('cameras', 'yolo_service_url'))->toBeTrue();
});

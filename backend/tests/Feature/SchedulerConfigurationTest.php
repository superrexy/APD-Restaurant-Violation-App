<?php

test('scheduler has camera:health-check command registered', function () {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
    $events = collect($schedule->events());

    $healthCheckEvent = $events->first(function ($event) {
        return str_contains($event->command, 'camera:health-check');
    });

    expect($healthCheckEvent)->not->toBeNull()
        ->and($healthCheckEvent->expression)->toBe('*/3 * * * * *');
});

test('scheduler uses withoutOverlapping for camera:health-check', function () {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
    $events = collect($schedule->events());

    $healthCheckEvent = $events->first(function ($event) {
        return str_contains($event->command, 'camera:health-check');
    });

    expect($healthCheckEvent)->not->toBeNull()
        ->and($healthCheckEvent->withoutOverlapping)->toBeTrue();
});

test('scheduler uses runInBackground for camera:health-check', function () {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
    $events = collect($schedule->events());

    $healthCheckEvent = $events->first(function ($event) {
        return str_contains($event->command, 'camera:health-check');
    });

    expect($healthCheckEvent)->not->toBeNull()
        ->and($healthCheckEvent->runInBackground)->toBeTrue();
});

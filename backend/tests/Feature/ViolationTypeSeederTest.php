<?php

use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

test('ViolationTypeSeeder creates all 6 violation types', function () {
    Artisan::call('db:seed', ['--class' => 'ViolationTypeSeeder']);

    expect(ViolationType::count())->toBe(6);

    $codes = ViolationType::pluck('code')->toArray();
    expect($codes)->toBeArray();
    expect($codes)->toHaveCount(6);
});

test('ViolationTypeSeeder creates correct violation codes', function () {
    Artisan::call('db:seed', ['--class' => 'ViolationTypeSeeder']);

    $codes = ViolationType::pluck('code')->toArray();

    expect($codes)->toContain('APRON');
    expect($codes)->toContain('HAIRNET');
    expect($codes)->toContain('MASK');
    expect($codes)->toContain('NO_APRON');
    expect($codes)->toContain('NO_HAIRNET');
    expect($codes)->toContain('NO_MASK');
});

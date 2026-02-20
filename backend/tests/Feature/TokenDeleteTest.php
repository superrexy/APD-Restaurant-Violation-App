<?php

use App\Models\User;

test('simple token deletion test', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Verify token works before deletion
    $beforeDelete = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');
    expect($beforeDelete->status())->toBe(200);

    // Delete all tokens
    $user->tokens()->delete();

    // Clear the auth guard cache to force re-validation
    $this->app['auth']->forgetGuards();

    // Verify token doesn't work after deletion
    $afterDelete = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');
    expect($afterDelete->status())->toBe(401);
});

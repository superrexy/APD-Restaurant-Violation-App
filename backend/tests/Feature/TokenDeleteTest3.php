<?php

use App\Models\User;

test('token deletion with forceDelete', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Delete using query builder
    User::find($user->id)->tokens()->delete();

    // Try the token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    expect($response->status())->toBe(401);
});

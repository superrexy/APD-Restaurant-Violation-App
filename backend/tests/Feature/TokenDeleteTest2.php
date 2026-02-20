<?php

use App\Models\User;

test('token deletion with fresh user instance', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Use fresh user instance and delete
    $freshUser = User::find($user->id);
    $freshUser->tokens()->delete();

    echo "\nToken count after delete: ".$freshUser->tokens()->count()."\n";

    // Try the token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    expect($response->status())->toBe(401);
});

<?php

use App\Models\User;

test('simulate actual logout test', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Logout using actingAs
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/logout');

    // Try to use the old token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo "\nResponse: ".$response->status()."\n";
    echo 'Tokens remaining: '.User::find($user->id)->tokens()->count()."\n";
});

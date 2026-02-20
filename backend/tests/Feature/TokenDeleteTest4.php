<?php

use App\Models\User;

test('logout token deletion debug', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Simulate logout
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/logout');

    // Check how many tokens remain
    $tokensAfter = User::find($user->id)->tokens()->count();
    echo "\nTokens after logout: ".$tokensAfter."\n";

    // Try to use the old token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo 'Response status: '.$response->status()."\n";
    expect($response->status())->toBe(401);
});

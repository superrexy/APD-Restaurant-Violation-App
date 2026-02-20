<?php

use App\Models\User;

test('logout via bearer token, then try again', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Logout using the ACTUAL token (Bearer), not actingAs
    $logoutResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/auth/logout');

    echo "\nLogout response: ".$logoutResponse->status()."\n";

    // Try to use the old token again
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo 'Response after logout: '.$response->status()."\n";
    echo 'Tokens remaining: '.User::find($user->id)->tokens()->count()."\n";
});

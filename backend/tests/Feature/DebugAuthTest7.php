<?php

use App\Models\User;

test('logout and clear cache', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Logout
    $logoutResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/auth/logout');

    // Check if there's a cache to clear
    if (\Cache::has('sanctum_token_'.$token)) {
        \Cache::forget('sanctum_token_'.$token);
        echo "\nCleared cache\n";
    }

    // Try to use the old token again
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo 'Response: '.$response->status()."\n";
});

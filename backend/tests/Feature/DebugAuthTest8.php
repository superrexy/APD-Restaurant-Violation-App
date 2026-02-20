<?php

use App\Models\User;

test('logout with forceDelete', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Logout using forceDelete
    $logoutResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/auth/logout');

    // Try to use the old token again
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo "\nResponse: ".$response->status()."\n";
    echo 'Tokens in DB: '.\DB::table('personal_access_tokens')->count()."\n";
});

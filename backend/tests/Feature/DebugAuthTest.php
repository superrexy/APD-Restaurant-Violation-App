<?php

use App\Models\User;

test('debug auth check', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Make a request with the token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo "\nResponse: ".$response->status()."\n";
    echo 'Response: '.json_encode($response->json())."\n";
});

<?php

use App\Models\User;

test('debug with same user instance', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Try deletion with same user instance
    foreach ($user->tokens()->get() as $t) {
        $t->delete();
    }

    echo "\nTokens after delete: ".$user->tokens()->count()."\n";

    // Try the token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo 'Response: '.$response->status()."\n";
});

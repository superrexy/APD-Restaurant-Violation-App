<?php

use App\Models\User;

test('logout with fresh user instance', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    // Logout using FRESH user instance
    $freshUser = User::find($user->id);
    foreach ($freshUser->tokens()->get() as $t) {
        $t->delete();
    }

    // Try to use the old token again
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo "\nResponse: ".$response->status()."\n";
});

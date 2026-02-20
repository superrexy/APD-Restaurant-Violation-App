<?php

use App\Models\User;

test('debug eloquent deletion', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    echo "\nTokens before: ".\DB::table('personal_access_tokens')->count()."\n";

    // Try different deletion methods
    // Method 1: Query delete
    // $user->tokens()->delete();

    // Method 2: First() delete
    // $user->tokens()->first()?->delete();

    // Method 3: Get all and delete each
    foreach ($user->tokens()->get() as $t) {
        $t->delete();
    }

    echo 'Tokens after: '.\DB::table('personal_access_tokens')->count()."\n";

    // Try the token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo 'Response: '.$response->status()."\n";
});

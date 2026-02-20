<?php

use App\Models\User;

test('debug deleted token', function () {
    // Check DB before creating anything
    echo "\nPersonal tokens before: ".\DB::table('personal_access_tokens')->count()."\n";

    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    echo 'Personal tokens after create: '.\DB::table('personal_access_tokens')->count()."\n";

    // Delete the token
    \DB::table('personal_access_tokens')->delete();
    echo 'Personal tokens after delete: '.\DB::table('personal_access_tokens')->count()."\n";

    // Try to use the deleted token
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');

    echo 'Response status: '.$response->status()."\n";
});

<?php

use App\Models\User;

test('token validation without refresh', function () {
    // Clean up first
    User::truncate();

    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Verify token works
    $beforeDelete = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');
    echo "\nBefore delete: ".$beforeDelete->status()."\n";

    // Delete all tokens via direct query
    \DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

    // Verify token doesn't work
    $afterDelete = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/users');
    echo 'After delete: '.$afterDelete->status()."\n";

    expect($afterDelete->status())->toBe(401);
});

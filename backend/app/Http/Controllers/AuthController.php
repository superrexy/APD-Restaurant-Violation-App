<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;

#[Group('Authentication', weight: 1)]
class AuthController extends Controller
{
    #[Endpoint(title: 'Login', description: 'Authenticate with email and password')]
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return $this->unauthorized('Invalid credentials');
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success(['token' => $token, 'user' => $user], 'Success');
    }

    /**
     * Logout and revoke current authentication token
     */
    #[Endpoint(title: 'Logout', description: 'Logout and revoke current token')]
    public function logout()
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return $this->noContent();
    }

    /**
     * Rotate authentication token - delete old, create new.
     */
    #[Endpoint(title: 'Refresh token', description: 'Rotate authentication token')]
    public function refreshToken()
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        $newToken = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $newToken,
            'user' => $user,
        ], 'Success');
    }
}

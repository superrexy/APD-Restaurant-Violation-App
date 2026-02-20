<?php

namespace App\Http\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Profile', weight: 1)]
class ProfileController extends Controller
{
    #[Endpoint(title: 'Get current user', description: 'Get the authenticated user profile')]
    public function show()
    {
        $user = auth()->user();

        return $this->success($user, 'Success');
    }
}

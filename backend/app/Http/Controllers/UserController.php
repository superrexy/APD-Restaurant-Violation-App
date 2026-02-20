<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Users', weight: 0)]
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Get paginated list of users
     */
    #[Endpoint(title: 'List users', description: 'Get paginated list of users')]
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $users = User::query()
            ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->filled('email'), fn ($q) => $q->where('email', 'like', '%'.$request->email.'%'))
            ->paginate($perPage);

        return $this->paginate($users, 'Success');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * Create a new user
     */
    #[Endpoint(title: 'Create user', description: 'Create a new user')]
    public function store(UserStoreRequest $request)
    {
        $user = User::create($request->validated());

        return $this->created($user, 'Success');
    }

    /**
     * Display the specified resource.
     *
     * Get a single user by ID
     */
    #[Endpoint(title: 'Get user', description: 'Get a single user by ID')]
    public function show(User $user)
    {
        return $this->success($user, 'Success');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * Update an existing user
     */
    #[Endpoint(title: 'Update user', description: 'Update an existing user')]
    public function update(UserUpdateRequest $request, User $user)
    {
        $user->update($request->validated());

        return $this->success($user, 'Success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Delete a user
     */
    #[Endpoint(title: 'Delete user', description: 'Delete a user')]
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot delete your own account');
        }

        $user->delete();

        return $this->noContent();
    }
}

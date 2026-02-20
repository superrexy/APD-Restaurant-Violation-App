<?php

namespace App\Http\Controllers;

use App\Http\Requests\ViolationTypeStoreRequest;
use App\Http\Requests\ViolationTypeUpdateRequest;
use App\Models\ViolationType;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Violation Types', weight: 1)]
class ViolationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Get paginated list of violation types
     */
    #[Endpoint(title: 'List violation types', description: 'Get paginated list of violation types')]
    public function index()
    {
        $violationTypes = ViolationType::paginate(10);

        return $this->paginate($violationTypes, 'Success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Create a new violation type
     */
    #[Endpoint(title: 'Create violation type', description: 'Create a new violation type')]
    public function store(ViolationTypeStoreRequest $request)
    {
        $violationType = ViolationType::create($request->validated());

        return $this->created($violationType, 'Success');
    }

    /**
     * Display the specified resource.
     *
     * Get a single violation type by ID
     */
    #[Endpoint(title: 'Get violation type', description: 'Get a single violation type by ID')]
    public function show(ViolationType $violationType)
    {
        return $this->success($violationType, 'Success');
    }

    /**
     * Update the specified resource in storage.
     *
     * Update an existing violation type
     */
    #[Endpoint(title: 'Update violation type', description: 'Update an existing violation type')]
    public function update(ViolationTypeUpdateRequest $request, ViolationType $violationType)
    {
        $violationType->update($request->validated());

        return $this->success($violationType, 'Success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Delete a violation type
     */
    #[Endpoint(title: 'Delete violation type', description: 'Delete a violation type')]
    public function destroy(ViolationType $violationType)
    {
        $violationType->delete();

        return $this->noContent();
    }
}

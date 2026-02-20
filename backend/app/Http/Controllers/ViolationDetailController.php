<?php

namespace App\Http\Controllers;

use App\Http\Requests\ViolationDetailUpdateStatusRequest;
use App\Models\ViolationDetail;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Violation Details', weight: 3)]
class ViolationDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Get paginated list of violation details
     */
    #[Endpoint(title: 'List violation details', description: 'Get paginated list of violation details')]
    public function index()
    {
        $violationDetails = ViolationDetail::with(['violation', 'violationType'])->paginate(10);

        return $this->paginate($violationDetails, 'Success');
    }

    /**
     * Display the specified resource.
     *
     * Get a single violation detail by ID
     */
    #[Endpoint(title: 'Get violation detail', description: 'Get a single violation detail by ID')]
    public function show(ViolationDetail $violationDetail)
    {
        $violationDetail->load(['violation', 'violationType']);

        return $this->success($violationDetail, 'Success');
    }

    /**
     * Update violation detail status.
     *
     * Update violation detail status (unverified, confirmed, dismissed)
     */
    #[Endpoint(title: 'Update violation detail status', description: 'Update violation detail status')]
    public function updateStatus(ViolationDetailUpdateStatusRequest $request, ViolationDetail $violationDetail)
    {
        $violationDetail->update($request->validated());

        return $this->success($violationDetail, 'Success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Delete a violation detail
     */
    #[Endpoint(title: 'Delete violation detail', description: 'Delete a violation detail')]
    public function destroy(ViolationDetail $violationDetail)
    {
        $violationDetail->delete();

        return $this->noContent();
    }
}

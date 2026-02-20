<?php

namespace App\Http\Controllers;

use App\Events\ViolationDetected;
use App\Http\Requests\ViolationStoreRequest;
use App\Http\Requests\ViolationUpdateStatusRequest;
use App\Models\Camera;
use App\Models\Violation;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

#[Group('Violations', weight: 2)]
class ViolationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Get paginated list of violations
     */
    #[Endpoint(title: 'List violations', description: 'Get paginated list of violations')]
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 12);
        $status = $request->input('status');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = Violation::with(['camera', 'violationDetails.violationType'])
            ->orderBy($sortBy, $sortOrder);

        if ($status && in_array($status, ['pending', 'reviewed', 'resolved'])) {
            $query->where('status', $status);
        }

        $violations = $query->paginate($perPage);

        return $this->paginate($violations, 'Success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Create a new violation with image and details
     */
    #[Endpoint(title: 'Create violation', description: 'Create a new violation with image and details')]
    public function store(ViolationStoreRequest $request)
    {
        $validated = $request->validated();

        // Handle image upload
        $imagePath = $request->file('image')->store('violations', 'public');

        // Find camera by code
        $camera = Camera::where('code', $validated['camera_code'])->firstOrFail();

        // Create violation
        $violation = Violation::create([
            'camera_id' => $camera->id,
            'image_path' => $imagePath,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create violation details
        foreach ($validated['violation_details'] as $detail) {
            $violationType = \App\Models\ViolationType::where('code', $detail['violation_code'])->firstOrFail();

            $violation->violationDetails()->create([
                'violation_type_id' => $violationType->id,
                'confidence_score' => $detail['confidence_score'] ?? null,
                'additional_info' => $detail['additional_info'] ?? null,
                'status' => 'unverified',
            ]);
        }

        return $this->created($violation->load(['camera', 'violationDetails.violationType']), 'Success');
    }

    /**
     * Display the specified resource.
     *
     * Get a single violation by ID with details
     */
    #[Endpoint(title: 'Get violation', description: 'Get a single violation by ID with details')]
    public function show(Violation $violation)
    {
        $violation->load(['camera', 'violationDetails.violationType']);

        return $this->success($violation, 'Success');
    }

    /**
     * Update violation status.
     *
     * Update violation status (pending, reviewed, resolved)
     */
    #[Endpoint(title: 'Update violation status', description: 'Update violation status')]
    public function updateStatus(ViolationUpdateStatusRequest $request, Violation $violation)
    {
        $violation->update($request->validated());

        return $this->success($violation, 'Success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Delete a violation
     */
    #[Endpoint(title: 'Delete violation', description: 'Delete a violation')]
    public function destroy(Violation $violation)
    {
        try {
            if ($violation->image_path) {
                Storage::disk('public')->delete($violation->image_path);
            }
        } catch (\Exception) {
            // Continue deletion even if file deletion fails
        }

        $violation->delete();

        return $this->noContent();
    }
}

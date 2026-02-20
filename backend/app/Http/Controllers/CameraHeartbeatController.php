<?php

namespace App\Http\Controllers;

use App\Http\Requests\CameraHeartbeatRequest;
use App\Models\Camera;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Cameras', weight: 1)]
class CameraHeartbeatController extends Controller
{
    /**
     * Store camera heartbeat.
     *
     * Update camera status and connection timestamp
     */
    #[Endpoint(title: 'Camera heartbeat', description: 'Update camera status and connection timestamp')]
    public function store(CameraHeartbeatRequest $request)
    {
        $validated = $request->validated();

        $camera = Camera::where('code', $validated['camera_code'])->first();

        if (!$camera) {
            return $this->notFound('Camera not found', 'Camera');
        }

        $updateData = [
            'connected_at' => now(),
        ];

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];

            if ($camera->status === 'inactive' && $validated['status'] === 'active') {
                $updateData['disconnected_at'] = null;
            }
        }

        $camera->update($updateData);

        return $this->success($camera, 'Heartbeat received');
    }
}

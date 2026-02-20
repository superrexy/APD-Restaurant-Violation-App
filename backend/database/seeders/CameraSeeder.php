<?php

namespace Database\Seeders;

use App\Models\Camera;
use Illuminate\Database\Seeder;

class CameraSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		Camera::create([
			'name' => 'Camera-001',
			'description' => '',
			'location' => 'Dapur Umum',
			'code' => 'CAM001',
			'status' => 'active',
			'connected_at' => '2026-02-19 07:08:13',
			'disconnected_at' => null,
			'last_maintenance_at' => null,
			'yolo_detection_status' => true,
			'yolo_service_url' => 'http://localhost:8081',
		]);
	}
}

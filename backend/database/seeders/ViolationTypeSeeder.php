<?php

namespace Database\Seeders;

use App\Models\ViolationType;
use Illuminate\Database\Seeder;

class ViolationTypeSeeder extends Seeder
{
	public function run(): void
	{
		$violationTypes = [
			[
				'code' => 'APRON',
				'name' => 'No Apron',
				'description' => 'Staff member not wearing required protective apron',
				'severity' => 'high',
				'is_active' => true,
			],
			[
				'code' => 'HAIRNET',
				'name' => 'No Hairnet',
				'description' => 'Staff member not wearing required hairnet',
				'severity' => 'high',
				'is_active' => true,
			],
			[
				'code' => 'MASK',
				'name' => 'No Mask',
				'description' => 'Staff member not wearing required protective mask',
				'severity' => 'medium',
				'is_active' => true,
			],
			[
				'code' => 'NO_APRON',
				'name' => 'No Apron Detected',
				'description' => 'Detected absence of apron on staff member',
				'severity' => 'high',
				'is_active' => true,
			],
			[
				'code' => 'NO_HAIRNET',
				'name' => 'No Hairnet Detected',
				'description' => 'Detected absence of hairnet on staff member',
				'severity' => 'high',
				'is_active' => true,
			],
			[
				'code' => 'NO_MASK',
				'name' => 'No Mask Detected',
				'description' => 'Detected absence of mask on staff member',
				'severity' => 'medium',
				'is_active' => true,
			],
		];

		foreach ($violationTypes as $violationType) {
			ViolationType::create([
				'code' => $violationType['code'],
				'name' => $violationType['name'],
				'description' => $violationType['description'],
				'severity' => $violationType['severity'],
				'is_active' => $violationType['is_active'],
			]);
		}
	}
}

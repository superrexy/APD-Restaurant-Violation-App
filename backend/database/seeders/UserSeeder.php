<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
	public function run(): void
	{
		$users = [
			['name' => 'Admin User', 'email' => 'admin@example.com', 'password' => 'password'],
		];

		foreach ($users as $user) {
			User::create([
				'name' => $user['name'],
				'email' => $user['email'],
				'password' => Hash::make($user['password']),
			]);
		}
	}
}

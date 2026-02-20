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
			['name' => 'John Doe', 'email' => 'john@example.com', 'password' => 'password'],
			['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => 'password'],
			['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'password' => 'password'],
			['name' => 'Alice Williams', 'email' => 'alice@example.com', 'password' => 'password'],
			['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'password' => 'password'],
			['name' => 'David Wilson', 'email' => 'david@example.com', 'password' => 'password'],
			['name' => 'Emma Davis', 'email' => 'emma@example.com', 'password' => 'password'],
			['name' => 'Frank Miller', 'email' => 'frank@example.com', 'password' => 'password'],
			['name' => 'Grace Lee', 'email' => 'grace@example.com', 'password' => 'password'],
			['name' => 'Henry Taylor', 'email' => 'henry@example.com', 'password' => 'password'],
			['name' => 'Ivy Chen', 'email' => 'ivy@example.com', 'password' => 'password'],
			['name' => 'Jack Robinson', 'email' => 'jack@example.com', 'password' => 'password'],
			['name' => 'Karen White', 'email' => 'karen@example.com', 'password' => 'password'],
			['name' => 'Larry King', 'email' => 'larry@example.com', 'password' => 'password'],
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

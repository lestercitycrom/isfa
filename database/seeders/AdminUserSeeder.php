<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{
	public function run(): void
	{
		$email = (string) (env('ADMIN_EMAIL') ?: 'admin@gmail.com');
		$password = (string) (env('ADMIN_PASSWORD') ?: 'admin123');

		User::query()->updateOrCreate(
			['email' => $email],
			[
				'name' => 'Admin',
				'password' => Hash::make($password),
			]
		);
	}
}

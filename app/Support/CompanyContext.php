<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

final class CompanyContext
{
	public static function user(): ?User
	{
		/** @var User|null $user */
		$user = auth()->user();

		return $user;
	}

	public static function isAdmin(): bool
	{
		$user = self::user();

		return $user?->isAdmin() ?? false;
	}

	public static function companyId(): ?int
	{
		$user = self::user();

		if ($user === null || $user->isAdmin()) {
			return null;
		}

		if ($user->company_id === null) {
			return null;
		}

		return (int) $user->company_id;
	}
}

<?php

declare(strict_types=1);

namespace App\Concerns;

use Spatie\Activitylog\Contracts\Activity;

trait LogsCompanyActivity
{
	public function tapActivity(Activity $activity, string $eventName): void
	{
		$companyId = $this->resolveActivityCompanyId();

		if ($companyId !== null) {
			$activity->company_id = $companyId;
		}
	}

	protected function resolveActivityCompanyId(): ?int
	{
		$companyId = $this->getAttribute('company_id');

		if ($companyId === null) {
			return null;
		}

		return (int) $companyId;
	}
}

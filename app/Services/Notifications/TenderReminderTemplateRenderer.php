<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Tender;

final class TenderReminderTemplateRenderer
{
	/**
	 * @return array<string, string>
	 */
	public function buildVariables(Tender $tender, int $daysLeft): array
	{
		$deadline = $tender->end_at ?? $tender->envelope_at;
		$tenderCode = trim((string) ($tender->document_number ?: $tender->event_id));

		return [
			'tender_code' => $tenderCode,
			'tender_title' => (string) $tender->title,
			'deadline_at' => $deadline?->format('Y-m-d H:i') ?? '-',
			'days_left' => (string) $daysLeft,
			'tender_url' => route('admin.tenders.show', ['tender' => $tender->id]),
		];
	}

	/**
	 * @param array<string, string> $variables
	 */
	public function render(string $template, array $variables): string
	{
		$replace = [];

		foreach ($variables as $key => $value) {
			$replace['{{' . $key . '}}'] = $value;
		}

		return strtr($template, $replace);
	}

	/**
	 * @return array<int, string>
	 */
	public function availablePlaceholders(): array
	{
		return [
			'{{tender_code}}',
			'{{tender_title}}',
			'{{deadline_at}}',
			'{{days_left}}',
			'{{tender_url}}',
		];
	}
}

<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Tender;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

final class ActivityLogFormatter
{
	/**
	 * @return array<string, string>
	 */
	private static function labelMap(): array
	{
		return [
			'name' => __('common.name'),
			'title' => __('common.title'),
			'description' => __('common.description'),
			'company_id' => __('common.company'),
			'category_id' => __('common.category'),
			'email' => __('common.email'),
			'phone' => __('common.phone'),
			'website' => __('common.website'),
			'contact_name' => __('common.contact_name'),
			'address' => __('common.address'),
			'comment' => __('common.comment'),
			'status' => __('common.status'),
			'terms' => __('common.price_terms'),
			'event_id' => __('common.event_id'),
			'organization_name' => __('common.organization'),
			'organization_voen' => __('common.voen'),
			'document_number' => __('common.document_number'),
			'published_at' => __('common.published_at'),
			'start_at' => __('common.start_at'),
			'end_at' => __('common.end_at'),
			'envelope_at' => __('common.envelope_at'),
			'estimated_amount' => __('common.amount'),
			'view_fee' => __('common.view_fee'),
			'participation_fee' => __('common.participation_fee'),
			'min_number_of_suppliers' => __('common.min_suppliers'),
		];
	}

	public static function labelFor(string $field): string
	{
		if (str_starts_with($field, 'common.') || str_starts_with($field, 'tenders.')) {
			$translated = __($field);
			if ($translated !== $field) {
				return $translated;
			}
		}

		$map = self::labelMap();

		if (isset($map[$field])) {
			return $map[$field];
		}

		$short = Str::of($field)->afterLast('.')->toString();
		if (isset($map[$short])) {
			return $map[$short];
		}

		return Str::of($short)->replace('_', ' ')->headline()->toString();
	}

	public static function eventLabel(?string $event): string
	{
		$event = (string) $event;

		return match ($event) {
			'created' => __('common.event_created'),
			'updated' => __('common.event_updated'),
			'deleted' => __('common.event_deleted'),
			'attached' => __('common.event_attached'),
			'detached' => __('common.event_detached'),
			default => $event !== '' ? $event : '—',
		};
	}

	public static function formatValue(mixed $value): string
	{
		if ($value === null || $value === '') {
			return '—';
		}

		if ($value instanceof DateTimeInterface) {
			return $value->format('Y-m-d H:i');
		}

		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}

		if (is_array($value)) {
			return (string) json_encode($value, JSON_UNESCAPED_UNICODE);
		}

		if (is_string($value)) {
			$trimmed = trim($value);
			if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $trimmed) === 1) {
				try {
					return Carbon::parse($trimmed)->format('Y-m-d H:i');
				} catch (\Throwable) {
					// fall through
				}
			}
		}

		return (string) $value;
	}

	public static function subjectTypeLabel(?string $subjectType): string
	{
		return match ((string) $subjectType) {
			Tender::class => __('common.tender'),
			Product::class => __('common.product'),
			ProductCategory::class => __('common.category'),
			Supplier::class => __('common.supplier'),
			User::class => __('common.company'),
			default => __('common.subject'),
		};
	}

	public static function subjectTitle(Activity $activity): string
	{
		$subject = $activity->subject;

		if ($subject === null) {
			return '#' . ((string) $activity->subject_id);
		}

		foreach (['name', 'title', 'company_name', 'event_id'] as $field) {
			$value = $subject->{$field} ?? null;
			if ($value !== null && trim((string) $value) !== '') {
				return (string) $value;
			}
		}

		return '#' . ((string) $activity->subject_id);
	}

	public static function summary(Activity $activity): string
	{
		$event = mb_strtolower(self::eventLabel($activity->event));
		$subject = mb_strtolower(self::subjectTypeLabel($activity->subject_type));
		$title = self::subjectTitle($activity);

		return __('common.activity_summary', [
			'event' => $event,
			'subject' => $subject,
			'title' => $title,
		]);
	}
}

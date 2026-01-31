<?php

namespace App\Services\Etender;

use App\Models\Tender;
use App\Models\TenderAnnouncement;
use App\Models\TenderContact;
use App\Models\TenderItem;
use App\Models\TenderPublishHistory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class EtenderEventSyncService
{
	public function __construct(
		private readonly EtenderApiClient $client,
		private readonly EtenderDictionaryService $dictionaryService
	) {
	}

	/**
	 * @throws Throwable
	 */
	public function sync(int $eventId): Tender
	{
		$base = $this->client->getBase($eventId);
		$event = $this->client->getEvent($eventId);
		$info = $this->client->getInfo($eventId);
		$contacts = $this->client->getContactPersons($eventId);
		$revokeHistory = $this->client->getRevokeHistory($eventId);
		$announcements = $this->client->getAnnouncements($eventId);

		$bomItems = $this->fetchAllBomLines($eventId);

		$tz = (string) config('etender.timezone', 'Asia/Baku');

		$eventTypeCode = $this->toCode($base['eventType'] ?? $event['eventType'] ?? null);
		$eventStatusCode = $this->toCode($base['eventStatus'] ?? null);
		$documentViewTypeCode = $this->toCode($base['documentViewType'] ?? null);

		$this->dictionaryService->touch(EtenderDictionaries::EVENT_TYPE, $eventTypeCode, [
			'sample_event_id' => $eventId,
			'sample_title' => (string) ($event['tenderName'] ?? $base['eventName'] ?? ''),
		]);

		$this->dictionaryService->touch(EtenderDictionaries::EVENT_STATUS, $eventStatusCode, [
			'sample_event_id' => $eventId,
			'sample_title' => (string) ($event['tenderName'] ?? $base['eventName'] ?? ''),
		]);

		$this->dictionaryService->touch(EtenderDictionaries::DOCUMENT_VIEW_TYPE, $documentViewTypeCode, [
			'sample_event_id' => $eventId,
			'sample_title' => (string) ($event['tenderName'] ?? $base['eventName'] ?? ''),
		]);

		return DB::transaction(function () use (
			$eventId,
			$base,
			$event,
			$info,
			$contacts,
			$revokeHistory,
			$announcements,
			$bomItems,
			$tz,
			$eventTypeCode,
			$eventStatusCode,
			$documentViewTypeCode
		): Tender {
			$tender = Tender::query()->updateOrCreate(
				['event_id' => $eventId],
				[
					'rfx_id' => $event['rfxId'] ?? null,
					'inner_event_id' => $event['eventId'] ?? null,

					'title' => (string) ($event['tenderName'] ?? $base['eventName'] ?? ''),
					'organization_name' => $event['organizationName'] ?? null,
					'organization_voen' => $event['organizationVoen'] ?? null,
					'address' => $event['address'] ?? null,

					'document_number' => $event['documentNumber'] ?? null,
					'document_version' => $base['documentVersion'] ?? null,

					'event_type_code' => $eventTypeCode,
					'event_status_code' => $eventStatusCode,
					'document_view_type_code' => $documentViewTypeCode,

					'estimated_amount' => $event['estimatedAmount'] ?? null,
					'min_number_of_suppliers' => $event['minNumberOfSuppliers'] ?? null,

					'published_at' => $this->fromUnixSeconds($event['publishDate'] ?? null, $tz),
					'start_at' => $this->fromUnixSeconds($event['startDate'] ?? null, $tz),
					'end_at' => $this->fromUnixSeconds($event['endDate'] ?? null, $tz),
					'envelope_at' => $this->fromUnixSeconds($event['envelopeDate'] ?? null, $tz),

					'view_fee' => $info['viewFee'] ?? null,
					'participation_fee' => $info['participationFee'] ?? null,

					'raw' => [
						'base' => $base,
						'event' => $event,
						'info' => $info,
						'contacts' => $contacts,
						'revoke_history' => $revokeHistory,
						'announcements' => $announcements,
						'bom_lines' => $bomItems,
					],
				]
			);

			$this->syncItems($tender, $bomItems);
			$this->syncContacts($tender, $contacts);
			$this->syncAnnouncements($tender, $announcements);
			$this->syncPublishHistory($tender, $revokeHistory, $tz);

			return $tender->fresh();
		});
	}

	private function fetchAllBomLines(int $eventId): array
	{
		$pageNumber = 1;
		$pageSize = 200;
		$all = [];

		while (true) {
			$page = $this->client->getBomLines($eventId, $pageNumber, $pageSize);

			$items = $page['items'] ?? [];
			if (is_array($items)) {
				foreach ($items as $row) {
					if (is_array($row)) {
						$all[] = $row;
					}
				}
			}

			$hasNext = (bool) ($page['hasNextPage'] ?? false);
			if (!$hasNext) {
				break;
			}

			$pageNumber++;
			if ($pageNumber > 200) {
				// Safety guard
				break;
			}
		}

		return $all;
	}

	private function syncItems(Tender $tender, array $items): void
	{
		$existingIds = [];

		foreach ($items as $item) {
			$externalId = $item['id'] ?? null;
			if ($externalId === null) {
				continue;
			}

			$existingIds[] = (int) $externalId;

			TenderItem::query()->updateOrCreate(
				[
					'tender_id' => $tender->id,
					'external_id' => (int) $externalId,
				],
				[
					'name' => $item['name'] ?? null,
					'description' => $item['description'] ?? null,
					'unit_of_measure' => $item['unitOfMeasure'] ?? null,
					'quantity' => $item['quantity'] ?? null,
					'category_code' => $this->toCode($item['categoryCode'] ?? null),
				]
			);
		}

		// Delete removed items
		if (!empty($existingIds)) {
			TenderItem::query()
				->where('tender_id', $tender->id)
				->whereNotIn('external_id', $existingIds)
				->delete();
		}
	}

	private function syncContacts(Tender $tender, array $contacts): void
	{
		TenderContact::query()
			->where('tender_id', $tender->id)
			->delete();

		foreach ($contacts as $c) {
			if (!is_array($c)) {
				continue;
			}

			TenderContact::query()->create([
				'tender_id' => $tender->id,
				'full_name' => $c['fullName'] ?? null,
				'position' => $c['position'] ?? null,
				'contact' => $c['contact'] ?? null,
				'phone_number' => $c['phoneNumber'] ?? null,
			]);
		}
	}

	private function syncAnnouncements(Tender $tender, array $announcementsPayload): void
	{
		$version = $announcementsPayload['announcementVersion'] ?? null;
		$list = $announcementsPayload['announcements'] ?? [];

		$existingIds = [];

		if (!is_array($list)) {
			$list = [];
		}

		foreach ($list as $a) {
			if (!is_array($a)) {
				continue;
			}

			$externalId = $a['id'] ?? null;
			if ($externalId === null) {
				continue;
			}

			$existingIds[] = (int) $externalId;

			TenderAnnouncement::query()->updateOrCreate(
				[
					'tender_id' => $tender->id,
					'external_id' => (int) $externalId,
				],
				[
					'announcement_version' => $version,
					'text' => $a['text'] ?? null,
				]
			);
		}

		if (!empty($existingIds)) {
			TenderAnnouncement::query()
				->where('tender_id', $tender->id)
				->whereNotIn('external_id', $existingIds)
				->delete();
		}
	}

	private function syncPublishHistory(Tender $tender, array $history, string $tz): void
	{
		TenderPublishHistory::query()
			->where('tender_id', $tender->id)
			->delete();

		foreach ($history as $row) {
			if (!is_array($row)) {
				continue;
			}

			$publishedAt = $row['publishDate'] ?? null;
			if (!is_string($publishedAt) || $publishedAt === '') {
				continue;
			}

			// API returns ISO string here
			$dt = CarbonImmutable::parse($publishedAt, $tz);

			TenderPublishHistory::query()->create([
				'tender_id' => $tender->id,
				'published_at' => $dt,
			]);
		}
	}

	private function fromUnixSeconds(mixed $value, string $tz): ?CarbonImmutable
	{
		if ($value === null || $value === '') {
			return null;
		}

		$seconds = (int) $value;
		if ($seconds <= 0) {
			return null;
		}

		// Store timestamps in UTC to avoid timezone double-shifts
		return CarbonImmutable::createFromTimestampUTC($seconds);
	}


	private function toCode(mixed $value): ?string
	{
		if ($value === null) {
			return null;
		}

		$code = (string) $value;
		$code = trim($code);

		return $code === '' ? null : $code;
	}
}

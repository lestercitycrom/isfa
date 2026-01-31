<?php

use App\Models\DictionaryValue;
use App\Models\Tender;
use App\Services\Etender\EtenderDictionaries;
use App\Services\Etender\EtenderEventSyncService;
use Illuminate\Support\Facades\Http;

it('syncs tender and related entities correctly', function (): void {
	config()->set('etender.base_url', 'https://etender.gov.az');
	config()->set('etender.timezone', 'Asia/Baku');
	config()->set('etender.timeout_seconds', 20);

	$eventId = 346012;

	$base = [
		'documentViewType' => 0,
		'eventType' => 7,
		'eventName' => 'Müxtəlif növ tibbi tullantıların daşınması və zərərsizləşdirilməsi xidmətlərinin satınalınması',
		'eventStatus' => 1,
		'documentVersion' => 8,
	];

	$event = [
		'id' => 346012,
		'rfxId' => 126405,
		'eventId' => 112174,
		'tenderName' => 'Müxtəlif növ tibbi tullantıların daşınması və zərərsizləşdirilməsi xidmətlərinin satınalınması',
		'organizationName' => '"NAFTALAN ŞƏHƏR MƏRKƏZİ XƏSTƏXANASI" PUBLİK HÜQUQİ ŞƏXS ',
		'organizationVoen' => '2800016651',
		'envelopeDate' => 1771606800,
		'endDate' => 1771606800,
		'publishDate' => 1769708558,
		'startDate' => 1769709600,
		'budgetCategoryCode' => null,
		'address' => 'Naftalan şəhər, İslam Həsənzadə küçəsi 197',
		'cpvCode' => null,
		'eventType' => 7,
		'isRedirectionAvailable' => true,
		'minNumberOfSuppliers' => 3,
		'estimatedAmount' => 20594.8,
		'recreatedFromRfxId' => null,
		'recreatedFromEventId' => null,
		'documentNumber' => '2026/AT/00303/V1',
		'recreatedFromDocumentNumber' => null,
		'evaluatedFinalScore' => null,
		'categoryCodes' => ['76121900 Təhlükəli tullantıların utilizasiyası'],
	];

	$info = [
		'viewFee' => 25,
		'participationFee' => 41.1896,
	];

	$bomPage = [
		'currentPage' => 1,
		'totalPages' => 1,
		'pageSize' => 200,
		'itemsInPage' => 4,
		'totalItems' => 4,
		'items' => [
			[
				'id' => 4416461,
				'name' => 'Tibbi tullantıların daşınması',
				'description' => 'B və C sinfi  üzrə',
				'unitOfMeasure' => 'km',
				'quantity' => 300,
				'categoryCode' => '76121901',
			],
			[
				'id' => 4416462,
				'name' => 'Tibbi tullantıların daşınması',
				'description' => 'D sinfi üzrə',
				'unitOfMeasure' => 'km',
				'quantity' => 300,
				'categoryCode' => '76121901',
			],
			[
				'id' => 4416463,
				'name' => 'Tibbi tullantıların zərərsizləşdirilməsi',
				'description' => 'B və C sinfi  üzrə',
				'unitOfMeasure' => 'm(3)',
				'quantity' => 80,
				'categoryCode' => '76121901',
			],
			[
				'id' => 4416464,
				'name' => 'Tibbi tullantıların zərərsizləşdirilməsi',
				'description' => 'D sinfi üzrə',
				'unitOfMeasure' => 'kg',
				'quantity' => 400,
				'categoryCode' => '76121901',
			],
		],
		'hasPreviousPage' => false,
		'hasNextPage' => false,
		'firstItem' => 1,
		'lastItem' => 4,
	];

	$revokeHistory = [
		[
			'eventId' => 346012,
			'publishDate' => '2026-01-29T17:42:38',
		],
	];

	$contacts = [
		[
			'fullName' => 'Qəmər Hüseynova',
			'contact' => 'satınalma.naftalanshmx@tabib.gov.az',
			'position' => 'Satınalma üzrə mütəxəssis',
			'phoneNumber' => '+994 10 467 47 41',
		],
	];

	$announcements = [
		'announcementVersion' => 2,
		'announcements' => [
			['id' => 1183601, 'text' => 'Announcement 1'],
			['id' => 1183602, 'text' => 'Announcement 2'],
		],
	];

	Http::fake([
		'https://etender.gov.az/api/events/346012/base' => Http::response($base, 200),
		'https://etender.gov.az/api/events/346012' => Http::response($event, 200),
		'https://etender.gov.az/api/events/346012/info' => Http::response($info, 200),
		'https://etender.gov.az/api/events/346012/bomLines*' => Http::response($bomPage, 200),
		'https://etender.gov.az/api/events/346012/revoke-history' => Http::response($revokeHistory, 200),
		'https://etender.gov.az/api/events/346012/contact-persons' => Http::response($contacts, 200),
		'https://etender.gov.az/api/events/346012/announcements' => Http::response($announcements, 200),
	]);

	/** @var EtenderEventSyncService $service */
	$service = app(EtenderEventSyncService::class);

	$tender = $service->sync($eventId);

	expect($tender)->toBeInstanceOf(Tender::class);
	expect($tender->event_id)->toBe($eventId);
	expect($tender->rfx_id)->toBe(126405);
	expect($tender->inner_event_id)->toBe(112174);

	// Core fields
	expect($tender->title)->toBe($event['tenderName']);
	expect($tender->organization_voen)->toBe('2800016651');
	expect($tender->document_number)->toBe('2026/AT/00303/V1');
	expect((int) $tender->document_version)->toBe(8);

	// Enum codes (stored as codes)
	expect($tender->event_type_code)->toBe('7');
	expect($tender->event_status_code)->toBe('1');
	expect($tender->document_view_type_code)->toBe('0');

	// Dates should keep correct timestamps
	expect($tender->published_at?->timestamp)->toBe(1769708558);
	expect($tender->start_at?->timestamp)->toBe(1769709600);
	expect($tender->end_at?->timestamp)->toBe(1771606800);
	expect($tender->envelope_at?->timestamp)->toBe(1771606800);

	// Fees
	expect((float) $tender->view_fee)->toBe(25.0);
	expect((float) $tender->participation_fee)->toBe(41.1896);

	// Related entities
	expect($tender->items()->count())->toBe(4);
	expect($tender->contacts()->count())->toBe(1);
	expect($tender->announcements()->count())->toBe(2);
	expect($tender->publishHistories()->count())->toBe(1);

	// Dictionaries were auto-touched
	expect(DictionaryValue::query()->where('dictionary', EtenderDictionaries::EVENT_TYPE)->where('code', '7')->exists())->toBeTrue();
	expect(DictionaryValue::query()->where('dictionary', EtenderDictionaries::EVENT_STATUS)->where('code', '1')->exists())->toBeTrue();
	expect(DictionaryValue::query()->where('dictionary', EtenderDictionaries::DOCUMENT_VIEW_TYPE)->where('code', '0')->exists())->toBeTrue();
});

it('does not overwrite admin labels in dictionaries', function (): void {
	config()->set('etender.base_url', 'https://etender.gov.az');
	config()->set('etender.timezone', 'Asia/Baku');

	// Pre-create dictionary label (admin-defined)
	DictionaryValue::query()->create([
		'dictionary' => EtenderDictionaries::EVENT_STATUS,
		'code' => '1',
		'label' => 'Active', // Admin label must survive sync
		'meta' => null,
	]);

	$eventId = 346012;

	Http::fake([
		'https://etender.gov.az/api/events/346012/base' => Http::response([
			'documentViewType' => 0,
			'eventType' => 7,
			'eventName' => 'X',
			'eventStatus' => 1,
			'documentVersion' => 8,
		], 200),

		'https://etender.gov.az/api/events/346012' => Http::response([
			'id' => 346012,
			'rfxId' => 126405,
			'eventId' => 112174,
			'tenderName' => 'X',
			'organizationName' => 'Org',
			'organizationVoen' => '1',
			'envelopeDate' => 1771606800,
			'endDate' => 1771606800,
			'publishDate' => 1769708558,
			'startDate' => 1769709600,
			'address' => 'Addr',
			'eventType' => 7,
			'minNumberOfSuppliers' => 3,
			'estimatedAmount' => 1,
			'documentNumber' => 'N',
			'categoryCodes' => [],
		], 200),

		'https://etender.gov.az/api/events/346012/info' => Http::response([
			'viewFee' => 1,
			'participationFee' => 2,
		], 200),

		'https://etender.gov.az/api/events/346012/bomLines*' => Http::response([
			'items' => [],
			'hasNextPage' => false,
		], 200),

		'https://etender.gov.az/api/events/346012/revoke-history' => Http::response([], 200),
		'https://etender.gov.az/api/events/346012/contact-persons' => Http::response([], 200),
		'https://etender.gov.az/api/events/346012/announcements' => Http::response([
			'announcementVersion' => 1,
			'announcements' => [],
		], 200),
	]);

	/** @var EtenderEventSyncService $service */
	$service = app(EtenderEventSyncService::class);

	$service->sync($eventId);

	$dict = DictionaryValue::query()
		->where('dictionary', EtenderDictionaries::EVENT_STATUS)
		->where('code', '1')
		->firstOrFail();

	expect($dict->label)->toBe('Active');
});

it('merges bomLines across pages', function (): void {
	config()->set('etender.base_url', 'https://etender.gov.az');
	config()->set('etender.timezone', 'Asia/Baku');

	$eventId = 346012;

	Http::fake([
		'https://etender.gov.az/api/events/346012/base' => Http::response([
			'documentViewType' => 0,
			'eventType' => 7,
			'eventName' => 'X',
			'eventStatus' => 1,
			'documentVersion' => 8,
		], 200),

		'https://etender.gov.az/api/events/346012' => Http::response([
			'id' => 346012,
			'rfxId' => 126405,
			'eventId' => 112174,
			'tenderName' => 'X',
			'organizationName' => 'Org',
			'organizationVoen' => '1',
			'envelopeDate' => 1771606800,
			'endDate' => 1771606800,
			'publishDate' => 1769708558,
			'startDate' => 1769709600,
			'address' => 'Addr',
			'eventType' => 7,
			'minNumberOfSuppliers' => 3,
			'estimatedAmount' => 1,
			'documentNumber' => 'N',
			'categoryCodes' => [],
		], 200),

		'https://etender.gov.az/api/events/346012/info' => Http::response([
			'viewFee' => 1,
			'participationFee' => 2,
		], 200),

		// Page 1: has next
		'https://etender.gov.az/api/events/346012/bomLines*PageNumber=1*' => Http::response([
			'items' => [
				[
					'id' => 1,
					'name' => 'A',
					'description' => null,
					'unitOfMeasure' => 'pcs',
					'quantity' => 1,
					'categoryCode' => 'C1',
				],
			],
			'hasNextPage' => true,
		], 200),

		// Page 2: last page
		'https://etender.gov.az/api/events/346012/bomLines*PageNumber=2*' => Http::response([
			'items' => [
				[
					'id' => 2,
					'name' => 'B',
					'description' => null,
					'unitOfMeasure' => 'pcs',
					'quantity' => 2,
					'categoryCode' => 'C1',
				],
			],
			'hasNextPage' => false,
		], 200),

		'https://etender.gov.az/api/events/346012/revoke-history' => Http::response([], 200),
		'https://etender.gov.az/api/events/346012/contact-persons' => Http::response([], 200),
		'https://etender.gov.az/api/events/346012/announcements' => Http::response([
			'announcementVersion' => 1,
			'announcements' => [],
		], 200),
	]);

	/** @var EtenderEventSyncService $service */
	$service = app(EtenderEventSyncService::class);

	$tender = $service->sync($eventId);

	expect($tender->items()->count())->toBe(2);
	expect($tender->items()->where('external_id', 1)->exists())->toBeTrue();
	expect($tender->items()->where('external_id', 2)->exists())->toBeTrue();
});

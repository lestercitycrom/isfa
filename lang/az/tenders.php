<?php

declare(strict_types=1);

return [
	'index' => [
		'title' => 'Tenderlər',
		'subtitle' => 'Tender siyahısı. Linki və ya eventId daxil edin — emal avtomatik başlanacaq.',
	],

	'import' => [
		'label' => 'Tender linki və ya eventId',
		'placeholder' => 'https://etender.gov.az/main/competition/detail/346012 və ya 346012',
		'hint_redirect' => 'Sinxronizasiyadan sonra siz avtomatik olaraq tenderin detallı səhifəsinə yönləndiriləcəksiniz.',
		'do_not_close_tab' => 'Səkməni bağlamayın',
	],

	'filters' => [
		'search' => 'Axtarış',
		'search_placeholder' => 'Ad, təşkilat, eventId, sənəd nömrəsi...',
		'type' => 'Növ',
		'status' => 'Status',
		'all' => 'Hamısı',
	],

	'table' => [
		'title' => 'Ad',
		'published' => 'Dərc olunub',
		'actions' => 'Əməliyyatlar',
		'empty' => 'Hələ tender yoxdur. Yuxarıya linki yapışdırın və “Əlavə et” düyməsini basın.',
	],

	'actions' => [
		'add' => 'Əlavə et',
		'parsing' => 'Emal olunur...',
		'open' => 'Aç',
		'sync_tender' => 'Sinxronlaşdır',
		'syncing' => 'Sinxronizasiya...',
	],

	'flash' => [
		'synced' => 'Tender sinxronlaşdırıldı: #:id',
		'deleted' => 'Tender silindi.',
	],

	'errors' => [
		'cant_extract_event_id' => 'eventId çıxarmaq alınmadı. https://etender.gov.az/main/competition/detail/346012 tipli link və ya sadəcə 346012 rəqəmi gözlənilir.',
		'sync_error' => 'Sinxronizasiya xətası: :message',
		'sync_failed' => 'Sinxronizasiya alınmadı (çıxış kodu sıfır deyil). Logları/çıxışı yoxlayın.',
		'tender_not_found' => 'Sinxronizasiya bitdi, amma tender verilənlər bazasında tapılmadı.',
		'unexpected_error' => 'Sinxronizasiya zamanı gözlənilməz xəta. Logları yoxlayın.',
	],

	'create' => [
		'title' => 'Tender əlavə et',
		'subtitle' => 'eventId daxil edin (məs. 346012) və sinxronizasiyanı başladın',
		'placeholder' => '346012',
		'last_output' => 'Son çıxış',
		'tip_title' => 'Məsləhət',
		'tip_text' => 'ID-ni bu URL-dən götürə bilərsiniz:',
	],
];

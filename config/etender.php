<?php

return [
	'base_url' => env('ETENDER_BASE_URL', 'https://etender.gov.az'),
	'timezone' => env('ETENDER_TIMEZONE', 'Asia/Baku'),
	'timeout_seconds' => (int) env('ETENDER_TIMEOUT', 20),
	'search_event_type' => env('ETENDER_SEARCH_EVENT_TYPE', '0'),
	'search_event_status' => env('ETENDER_SEARCH_EVENT_STATUS', '0'),
	'search_pages' => (int) env('ETENDER_SEARCH_PAGES', 5),
	'search_page_size' => (int) env('ETENDER_SEARCH_PAGE_SIZE', 100),
	'search_days_back' => (int) env('ETENDER_SEARCH_DAYS_BACK', 30),
	'search_active_only' => (bool) env('ETENDER_SEARCH_ACTIVE_ONLY', true),
];

<?php

return [
	'base_url' => env('ETENDER_BASE_URL', 'https://etender.gov.az'),
	'timezone' => env('ETENDER_TIMEZONE', 'Asia/Baku'),
	'timeout_seconds' => (int) env('ETENDER_TIMEOUT', 20),
];

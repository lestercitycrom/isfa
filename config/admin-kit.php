<?php

declare(strict_types=1);

return [
	'brand' => [
		'name' => env('APP_NAME', 'Admin'),
		'badge' => env('ADMIN_KIT_BRAND_BADGE', 'SP'),
	],

	'layout' => [
		'container' => env('ADMIN_KIT_CONTAINER', 'max-w-7xl'),
	],

	'features' => [
		'global_search' => false,
		'quick_actions' => false,
		'table_density_toggle' => false,
		'filters_panel' => true,
	],

	'global_search' => [
		'enabled' => false,
		'route' => 'admin.products.index',
		'query_key' => 'q',
		'placeholder' => 'common.search_product', // Will be translated in view
	],

	'quick_actions' => [],

	/**
	 * Navigation items.
	 * Each item: ['label' => 'common.products', 'route' => 'admin.products.index', 'icon' => 'list']
	 */
	'nav' => [
		['label' => 'common.dashboard', 'route' => 'admin.dashboard', 'icon' => 'activity'],
		['label' => 'common.tenders', 'route' => 'admin.tenders.index', 'icon' => 'file-text'],
		['label' => 'common.products', 'route' => 'admin.products.index', 'icon' => 'box'],
		['label' => 'common.categories', 'route' => 'admin.categories.index', 'icon' => 'tag'],
		['label' => 'common.suppliers', 'route' => 'admin.suppliers.index', 'icon' => 'truck'],
		['label' => 'common.activity_log', 'route' => 'admin.activity_logs.index', 'icon' => 'activity'],
		['label' => 'common.companies', 'route' => 'admin.companies.index', 'icon' => 'building', 'admin_only' => true],
	],
];

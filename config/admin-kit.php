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
		['label' => 'common.tenders', 'route' => 'admin.tenders.index', 'icon' => 'file-text'],
		['label' => 'common.my_keyword_subscriptions', 'route' => 'admin.tenders.subscriptions', 'icon' => 'bell', 'company_only' => true],
		[
			'label' => 'common.products',
			'route' => 'admin.products.index',
			'icon' => 'box',
			'children' => [
				['label' => 'common.categories', 'route' => 'admin.categories.index', 'icon' => 'tag'],
				['label' => 'common.suppliers', 'route' => 'admin.suppliers.index', 'icon' => 'truck'],
			],
		],
		['label' => 'common.logs', 'route' => 'admin.activity_logs.index', 'icon' => 'activity'],
		['label' => 'common.companies', 'route' => 'admin.companies.index', 'icon' => 'building', 'admin_only' => true],
		['label' => 'common.settings', 'route' => 'admin.settings.notifications', 'icon' => 'settings', 'admin_only' => true],
	],
];

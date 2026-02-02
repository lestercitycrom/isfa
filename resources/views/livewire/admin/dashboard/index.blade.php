<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.dashboard')"
		:subtitle="$isAdmin ? __('common.dashboard_subtitle_admin') : __('common.dashboard_subtitle_company')"
	/>

	<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
		<x-admin.card>
			<div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.products') }}</div>
			<div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($productsCount) }}</div>
		</x-admin.card>

		<x-admin.card>
			<div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.suppliers') }}</div>
			<div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($suppliersCount) }}</div>
		</x-admin.card>

		<x-admin.card>
			<div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.categories') }}</div>
			<div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($categoriesCount) }}</div>
		</x-admin.card>

		<x-admin.card>
			<div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.tenders') }}</div>
			<div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($tendersCount) }}</div>
		</x-admin.card>
	</div>

	<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
		<x-admin.card>
			<div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.tenders_last_30_days') }}</div>
			<div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($tendersLast30Days) }}</div>
		</x-admin.card>

		<x-admin.card>
			<div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.linked_products') }}</div>
			<div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($linkedProductsCount) }}</div>
		</x-admin.card>

		<x-admin.card>
			<div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.coverage') }}</div>
			<div class="mt-2 text-2xl font-bold text-slate-900">{{ $coverage }}%</div>
		</x-admin.card>
	</div>

	<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
		<x-admin.card :title="__('common.top_categories')">
			<div class="space-y-3">
				@forelse ($topCategories as $row)
					<div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 px-3 py-2">
						<div class="truncate text-sm font-medium text-slate-900">{{ $row->name ?: '-' }}</div>
						<x-admin.badge variant="blue">{{ (int) $row->products_count }}</x-admin.badge>
					</div>
				@empty
					<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
				@endforelse
			</div>
		</x-admin.card>

		<x-admin.card :title="__('common.top_suppliers')">
			<div class="space-y-3">
				@forelse ($topSuppliers as $row)
					<div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 px-3 py-2">
						<div class="truncate text-sm font-medium text-slate-900">{{ $row->name ?: '-' }}</div>
						<x-admin.badge variant="green">{{ (int) $row->links_count }}</x-admin.badge>
					</div>
				@empty
					<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
				@endforelse
			</div>
		</x-admin.card>
	</div>

	<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
		<x-admin.card :title="__('common.tender_status_breakdown')">
			<div class="mb-3 text-xs text-slate-500">{{ __('common.tender_status_breakdown_hint') }}</div>
			<div class="space-y-3">
				@forelse ($statusStats as $row)
					@php
						$count = (int) $row->total;
						$percent = $statusTotal > 0 ? (int) round(($count / $statusTotal) * 100) : 0;
						$label = $statusLabels[$row->status_code] ?? ($row->status_code !== '' ? $row->status_code : '-');
					@endphp
					<div class="rounded-xl border border-slate-200 px-3 py-2">
						<div class="flex items-center justify-between gap-4">
							<div class="truncate text-sm font-medium text-slate-900">{{ $label }}</div>
							<div class="flex items-center gap-2">
								<x-admin.badge variant="gray">{{ $count }}</x-admin.badge>
								<span class="text-xs font-semibold text-slate-500">{{ $percent }}%</span>
							</div>
						</div>
						<div class="mt-2 h-2 rounded-full bg-slate-100">
							<div class="h-2 rounded-full bg-slate-400" style="width: {{ $percent }}%"></div>
						</div>
					</div>
				@empty
					<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
				@endforelse
			</div>
		</x-admin.card>

		<x-admin.card :title="__('common.activity_log')">
			<div class="space-y-3">
				@forelse ($latestActivities as $activity)
					<div class="rounded-xl border border-slate-200 px-3 py-2">
						<div class="flex items-center justify-between gap-3">
							<div class="text-sm font-semibold text-slate-900">{{ $activity->event ?: '-' }}</div>
							<div class="text-xs text-slate-500">{{ $activity->created_at?->format('Y-m-d H:i') }}</div>
						</div>
						<div class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $activity->description ?: '-' }}</div>
					</div>
				@empty
					<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
				@endforelse
			</div>
		</x-admin.card>
	</div>
</div>

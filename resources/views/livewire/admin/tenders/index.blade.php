<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.tenders')"
		:subtitle="__('common.tenders_subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" wire:click="resetFilters">
				{{ __('common.reset') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.filters-bar>
		<div class="lg:col-span-4">
			<x-admin.filter-input
				wire:model.live="search"
				:placeholder="__('common.search')"
				icon="search"
			/>
		</div>

		<div class="lg:col-span-3">
			<x-admin.filter-select
				wire:model.live="eventTypeFilter"
				label="{{ __('common.event_type') }}"
				icon="database"
			>
				<option value="">{{ __('common.all') }}</option>
				@foreach ($eventTypes as $row)
					@php
						$label = $row->label !== null && trim($row->label) !== '' ? $row->label : $row->code;
					@endphp
					<option value="{{ $row->code }}">{{ $label }}</option>
				@endforeach
			</x-admin.filter-select>
		</div>

		<div class="lg:col-span-3">
			<x-admin.filter-select
				wire:model.live="eventStatusFilter"
				label="{{ __('common.status') }}"
				icon="flag"
			>
				<option value="">{{ __('common.all') }}</option>
				@foreach ($eventStatuses as $row)
					@php
						$label = $row->label !== null && trim($row->label) !== '' ? $row->label : $row->code;
					@endphp
					<option value="{{ $row->code }}">{{ $label }}</option>
				@endforeach
			</x-admin.filter-select>
		</div>

		<div class="lg:col-span-2">
			<x-admin.button
				variant="secondary"
				wire:click="resetFilters"
				class="w-full"
			>
				{{ __('common.reset') }}
			</x-admin.button>
		</div>
	</x-admin.filters-bar>

	<x-admin.card>
		<x-admin.table :zebra="true" :sticky="true">
			<x-slot name="head">
				<tr>
					<x-admin.th nowrap>#</x-admin.th>
					<x-admin.th>{{ __('common.title') }}</x-admin.th>
					<x-admin.th>{{ __('common.organization') }}</x-admin.th>
					<x-admin.th nowrap>{{ __('common.published_at') }}</x-admin.th>
					<x-admin.th nowrap>{{ __('common.amount') }}</x-admin.th>
					<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($tenders as $tender)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td class="text-slate-600" nowrap>
						<div class="font-medium text-slate-900">{{ $tender->event_id }}</div>
						@if($tender->document_number)
							<div class="mt-1 text-xs text-slate-500">{{ $tender->document_number }}</div>
						@endif
					</x-admin.td>

					<x-admin.td>
						<div class="font-medium text-slate-900">{{ $tender->title }}</div>

						<div class="mt-2 flex flex-wrap gap-2">
							@if($tender->event_type_code)
								<x-admin.badge variant="blue">
									{{ __('common.event_type') }}: {{ $tender->event_type_code }}
								</x-admin.badge>
							@endif

							@if($tender->event_status_code)
								<x-admin.badge variant="gray">
									{{ __('common.status') }}: {{ $tender->event_status_code }}
								</x-admin.badge>
							@endif
						</div>
					</x-admin.td>

					<x-admin.td>
						@if($tender->organization_name)
							<div class="font-medium text-slate-900">{{ $tender->organization_name }}</div>
						@endif

						<div class="mt-1 text-xs text-slate-500">
							@if($tender->organization_voen)
								<span>VOEN: {{ $tender->organization_voen }}</span>
							@endif

							@if($tender->address)
								<div class="mt-1 line-clamp-2">{{ $tender->address }}</div>
							@endif
						</div>
					</x-admin.td>

					<x-admin.td nowrap class="text-slate-700">
						@if($tender->published_at)
							{{ $tender->published_at->format('Y-m-d H:i') }}
						@else
							<span class="text-slate-400">—</span>
						@endif
					</x-admin.td>

					<x-admin.td nowrap class="text-slate-700">
						@if($tender->estimated_amount !== null)
							{{ number_format((float) $tender->estimated_amount, 2, '.', ' ') }}
						@else
							<span class="text-slate-400">—</span>
						@endif
					</x-admin.td>

					<x-admin.td align="right" nowrap>
						<x-admin.table-actions
							:viewHref="route('admin.tenders.show', $tender)"
						/>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="6" class="text-center py-8 text-slate-500">
						{{ __('common.no_records') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		<div class="mt-4">
			{{ $tenders->links('pagination.admin') }}
		</div>
	</x-admin.card>
</div>

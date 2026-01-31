<div class="space-y-6">
	<x-admin.page-header
		:title="$tender->title"
		:subtitle="$tender->organization_name ?: __('common.tender')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.tenders.index')">
				{{ __('common.back') }}
			</x-admin.button>

			<a
				class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ $originalUrl }}"
				target="_blank"
				rel="noopener noreferrer"
			>
				<x-admin.icon name="external-link" class="h-4 w-4" />
				{{ __('common.open_original') }}
			</a>
		</x-slot>
	</x-admin.page-header>

	<x-admin.card :title="__('common.summary')">
		<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
			<div class="rounded-2xl border border-slate-200 bg-white p-4">
				<div class="text-xs text-slate-500">{{ __('common.event_id') }}</div>
				<div class="mt-1 text-lg font-semibold text-slate-900">{{ $tender->event_id }}</div>

				@if($tender->document_number)
					<div class="mt-2 text-xs text-slate-500">{{ __('common.document_number') }}</div>
					<div class="mt-1 text-sm font-medium text-slate-800">{{ $tender->document_number }}</div>
				@endif
			</div>

			<div class="rounded-2xl border border-slate-200 bg-white p-4">
				<div class="text-xs text-slate-500">{{ __('common.organization') }}</div>
				<div class="mt-1 text-sm font-semibold text-slate-900">
					{{ $tender->organization_name ?: '—' }}
				</div>

				@if($tender->organization_voen)
					<div class="mt-2 text-xs text-slate-500">VOEN</div>
					<div class="mt-1 text-sm font-medium text-slate-800">{{ $tender->organization_voen }}</div>
				@endif
			</div>

			<div class="rounded-2xl border border-slate-200 bg-white p-4">
				<div class="text-xs text-slate-500">{{ __('common.amount') }}</div>
				<div class="mt-1 text-lg font-semibold text-slate-900">
					@if($tender->estimated_amount !== null)
						{{ number_format((float) $tender->estimated_amount, 2, '.', ' ') }}
					@else
						—
					@endif
				</div>

				<div class="mt-3 flex flex-wrap gap-2">
					@if($tender->event_type_code)
						@php
							$typeLabel = $dictLabels['event_type'][$tender->event_type_code] ?? $tender->event_type_code;
						@endphp
						<x-admin.badge variant="blue">{{ __('common.event_type') }}: {{ $typeLabel }}</x-admin.badge>
					@endif

					@if($tender->event_status_code)
						@php
							$statusLabel = $dictLabels['event_status'][$tender->event_status_code] ?? $tender->event_status_code;
						@endphp
						<x-admin.badge variant="gray">{{ __('common.status') }}: {{ $statusLabel }}</x-admin.badge>
					@endif

					@if($tender->document_view_type_code)
						@php
							$viewLabel = $dictLabels['document_view_type'][$tender->document_view_type_code] ?? $tender->document_view_type_code;
						@endphp
						<x-admin.badge variant="green">{{ __('common.document_view_type') }}: {{ $viewLabel }}</x-admin.badge>
					@endif
				</div>
			</div>
		</div>

		<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
			<div class="rounded-2xl border border-slate-200 bg-white p-4">
				<div class="text-sm font-semibold text-slate-900">{{ __('common.dates') }}</div>
				<div class="mt-3 space-y-2 text-sm text-slate-700">
					<div class="flex items-center justify-between gap-4">
						<span class="text-slate-500">{{ __('common.published_at') }}</span>
						<span class="font-medium">{{ $tender->published_at?->format('Y-m-d H:i') ?? '—' }}</span>
					</div>
					<div class="flex items-center justify-between gap-4">
						<span class="text-slate-500">{{ __('common.start_at') }}</span>
						<span class="font-medium">{{ $tender->start_at?->format('Y-m-d H:i') ?? '—' }}</span>
					</div>
					<div class="flex items-center justify-between gap-4">
						<span class="text-slate-500">{{ __('common.end_at') }}</span>
						<span class="font-medium">{{ $tender->end_at?->format('Y-m-d H:i') ?? '—' }}</span>
					</div>
					<div class="flex items-center justify-between gap-4">
						<span class="text-slate-500">{{ __('common.envelope_at') }}</span>
						<span class="font-medium">{{ $tender->envelope_at?->format('Y-m-d H:i') ?? '—' }}</span>
					</div>
				</div>
			</div>

			<div class="rounded-2xl border border-slate-200 bg-white p-4">
				<div class="text-sm font-semibold text-slate-900">{{ __('common.fees') }}</div>
				<div class="mt-3 space-y-2 text-sm text-slate-700">
					<div class="flex items-center justify-between gap-4">
						<span class="text-slate-500">{{ __('common.view_fee') }}</span>
						<span class="font-medium">
							@if($tender->view_fee !== null)
								{{ number_format((float) $tender->view_fee, 2, '.', ' ') }}
							@else
								—
							@endif
						</span>
					</div>
					<div class="flex items-center justify-between gap-4">
						<span class="text-slate-500">{{ __('common.participation_fee') }}</span>
						<span class="font-medium">
							@if($tender->participation_fee !== null)
								{{ number_format((float) $tender->participation_fee, 2, '.', ' ') }}
							@else
								—
							@endif
						</span>
					</div>
					<div class="flex items-center justify-between gap-4">
						<span class="text-slate-500">{{ __('common.min_suppliers') }}</span>
						<span class="font-medium">{{ $tender->min_number_of_suppliers ?? '—' }}</span>
					</div>
				</div>
			</div>
		</div>

		@if($tender->address)
			<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
				<div class="text-sm font-semibold text-slate-900">{{ __('common.address') }}</div>
				<div class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $tender->address }}</div>
			</div>
		@endif
	</x-admin.card>

	<x-admin.card :title="__('common.items')">
		<x-admin.table :zebra="true">
			<x-slot name="head">
				<tr>
					<x-admin.th nowrap>#</x-admin.th>
					<x-admin.th>{{ __('common.name') }}</x-admin.th>
					<x-admin.th nowrap>{{ __('common.unit') }}</x-admin.th>
					<x-admin.th nowrap>{{ __('common.quantity') }}</x-admin.th>
					<x-admin.th nowrap>{{ __('common.category_code') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($tender->items as $item)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td nowrap class="text-slate-600">{{ $item->external_id }}</x-admin.td>
					<x-admin.td>
						<div class="font-medium text-slate-900">{{ $item->name ?: '—' }}</div>
						@if($item->description)
							<div class="mt-1 text-xs text-slate-500 whitespace-pre-line">{{ $item->description }}</div>
						@endif
					</x-admin.td>
					<x-admin.td nowrap class="text-slate-700">{{ $item->unit_of_measure ?: '—' }}</x-admin.td>
					<x-admin.td nowrap class="text-slate-700">
						@if($item->quantity !== null)
							{{ rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ' '), '0'), '.') }}
						@else
							—
						@endif
					</x-admin.td>
					<x-admin.td nowrap class="text-slate-700">{{ $item->category_code ?: '—' }}</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="5" class="text-center py-8 text-slate-500">
						{{ __('common.no_records') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>
	</x-admin.card>

	<x-admin.card :title="__('common.contacts')">
		<x-admin.table :zebra="true">
			<x-slot name="head">
				<tr>
					<x-admin.th>{{ __('common.full_name') }}</x-admin.th>
					<x-admin.th>{{ __('common.position') }}</x-admin.th>
					<x-admin.th>{{ __('common.contact') }}</x-admin.th>
					<x-admin.th nowrap>{{ __('common.phone') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($tender->contacts as $c)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td class="font-medium text-slate-900">{{ $c->full_name ?: '—' }}</x-admin.td>
					<x-admin.td class="text-slate-700">{{ $c->position ?: '—' }}</x-admin.td>
					<x-admin.td class="text-slate-700">
						@if($c->contact)
							{{ $c->contact }}
						@else
							—
						@endif
					</x-admin.td>
					<x-admin.td nowrap class="text-slate-700">{{ $c->phone_number ?: '—' }}</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="4" class="text-center py-8 text-slate-500">
						{{ __('common.no_records') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>
	</x-admin.card>

	<x-admin.card :title="__('common.announcements')">
		<div class="space-y-3">
			@forelse ($tender->announcements as $a)
				<div class="rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-xs text-slate-500">
						{{ __('common.announcement_id') }}: {{ $a->external_id ?? '—' }}
						@if($a->announcement_version !== null)
							<span class="ml-2">• {{ __('common.version') }}: {{ $a->announcement_version }}</span>
						@endif
					</div>
					<div class="mt-2 text-sm text-slate-800 whitespace-pre-line">{{ $a->text ?: '—' }}</div>
				</div>
			@empty
				<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
			@endforelse
		</div>
	</x-admin.card>

	<x-admin.card :title="__('common.publish_history')">
		<div class="space-y-2">
			@forelse ($tender->publishHistories as $h)
				<div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-3">
					<div class="text-sm font-medium text-slate-900">{{ $h->published_at?->format('Y-m-d H:i') ?? '—' }}</div>
					<div class="text-xs text-slate-500">UTC</div>
				</div>
			@empty
				<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
			@endforelse
		</div>
	</x-admin.card>
</div>

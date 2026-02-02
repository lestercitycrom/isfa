<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.activity_log')"
		:subtitle="__('common.activity_log_subtitle')"
	/>

	<x-admin.filters-bar :title="__('common.filters')">
		<div class="lg:col-span-5">
			<x-admin.filter-input
				label="{{ __('common.search') }}"
				placeholder="{{ __('common.search_activity') }}"
				icon="search"
				wire:model.live.debounce.300ms="search"
			/>
		</div>
		<div class="lg:col-span-3">
			<x-admin.filter-select label="{{ __('common.event') }}" wire:model.live="event">
				<option value="">{{ __('common.all_events') }}</option>
				@foreach($eventOptions as $value => $label)
					<option value="{{ $value }}">{{ $label }}</option>
				@endforeach
			</x-admin.filter-select>
		</div>
		<div class="lg:col-span-4">
			<x-admin.filter-select label="{{ __('common.subject') }}" wire:model.live="subjectType">
				<option value="">{{ __('common.all_subjects') }}</option>
				@foreach($subjectOptions as $value => $label)
					<option value="{{ $value }}">{{ $label }}</option>
				@endforeach
			</x-admin.filter-select>
		</div>
		<div class="lg:col-span-2">
			<x-admin.filter-input
				label="ID"
				placeholder="ID"
				wire:model.live="subjectId"
			/>
		</div>
	</x-admin.filters-bar>

	<x-admin.card>
		<x-admin.table :zebra="true">
			<x-slot name="head">
				<tr>
					<x-admin.th nowrap>{{ __('common.time') }}</x-admin.th>
					<x-admin.th>{{ __('common.event') }}</x-admin.th>
					<x-admin.th>{{ __('common.subject') }}</x-admin.th>
					<x-admin.th>{{ __('common.description') }}</x-admin.th>
					<x-admin.th>{{ __('common.user') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse($activities as $activity)
					@php
						$subject = $activity->subject;
						$subjectLabel = \App\Support\ActivityLogFormatter::subjectTitle($activity);
						$subjectTypeLabel = \App\Support\ActivityLogFormatter::subjectTypeLabel($activity->subject_type);
						$eventLabel = \App\Support\ActivityLogFormatter::eventLabel($activity->event);
						$summary = \App\Support\ActivityLogFormatter::summary($activity);
						$rawDescription = (string) $activity->description;
						$rawEvent = (string) $activity->event;
						$hideDescription = $rawDescription === '' || $rawDescription === $rawEvent || in_array($rawDescription, ['created', 'updated', 'deleted', 'attached', 'detached'], true);
						$changes = $activity->changes->get('attributes', []);
						$oldValues = $activity->changes->get('old', []);
						$hasDetails = !$hideDescription || (is_array($changes) && count($changes) > 0);
						$subjectRoute = null;

					if ($activity->subject_type === \App\Models\Tender::class) {
						$subjectRoute = route('admin.tenders.show', $activity->subject_id);
					} elseif ($activity->subject_type === \App\Models\Product::class) {
						$subjectRoute = route('admin.products.show', $activity->subject_id);
					} elseif ($activity->subject_type === \App\Models\Supplier::class) {
						$subjectRoute = route('admin.suppliers.show', $activity->subject_id);
					} elseif ($activity->subject_type === \App\Models\User::class && \App\Support\CompanyContext::isAdmin()) {
						$subjectRoute = route('admin.companies.show', $activity->subject_id);
					}
				@endphp

				<tr class="hover:bg-slate-50/70">
					<x-admin.td nowrap class="text-slate-600">
						{{ $activity->created_at?->format('Y-m-d H:i') ?? '—' }}
					</x-admin.td>
					<x-admin.td>
						<div class="text-sm font-semibold text-slate-900">{{ $eventLabel }}</div>
						<div class="mt-1 text-xs text-slate-500">{{ $subjectTypeLabel }}</div>
					</x-admin.td>
					<x-admin.td>
						@if($subjectRoute)
							<a href="{{ $subjectRoute }}" class="text-sm font-semibold text-slate-900 hover:underline">
								{{ $subjectLabel }}
							</a>
						@else
							<div class="text-sm font-semibold text-slate-900">{{ $subjectLabel }}</div>
						@endif
						<div class="mt-1 text-xs text-slate-500">ID: {{ $activity->subject_id ?? '—' }}</div>
					</x-admin.td>
					<x-admin.td>
						<div class="text-sm font-semibold text-slate-900">{{ $summary }}</div>
						@if($hasDetails)
							<details class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-2">
								<summary class="cursor-pointer text-xs font-semibold text-slate-600">
									{{ __('common.details') }}
								</summary>
								<div class="mt-2 space-y-2 text-xs text-slate-600">
									@if(!$hideDescription)
										<div>{{ $rawDescription }}</div>
									@endif
									@if(is_array($changes) && count($changes) > 0)
										@foreach($changes as $field => $newValue)
											@php
												$label = \App\Support\ActivityLogFormatter::labelFor((string) $field);
												$oldValue = \App\Support\ActivityLogFormatter::formatValue($oldValues[$field] ?? null);
												$newValueFormatted = \App\Support\ActivityLogFormatter::formatValue($newValue);
											@endphp
											<div class="flex flex-wrap items-center gap-2">
												<span class="rounded bg-white px-2 py-0.5 font-semibold text-slate-700">{{ $label }}</span>
												@if($oldValue !== '-')
													<span>{{ $oldValue }}</span>
												@endif
												<span class="text-slate-400">-></span>
												<span class="font-medium text-slate-800">{{ $newValueFormatted }}</span>
											</div>
										@endforeach
									@endif
								</div>
							</details>
						@endif
					</x-admin.td>
					<x-admin.td>
						<div class="text-sm font-medium text-slate-900">{{ $activity->causer?->name ?? '—' }}</div>
						<div class="mt-1 text-xs text-slate-500">{{ $activity->causer?->email }}</div>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="5" class="text-center py-10 text-slate-500">
						{{ __('common.no_records') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		@if($activities->hasPages())
			<div class="px-4 py-4">
				{{ $activities->links('pagination.admin') }}
			</div>
		@endif
	</x-admin.card>
</div>

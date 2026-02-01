@php
	$formatValue = fn ($value) => \App\Support\ActivityLogFormatter::formatValue($value);
	$labelFor = fn (string $field) => \App\Support\ActivityLogFormatter::labelFor($field);
	$eventLabel = fn (?string $event) => \App\Support\ActivityLogFormatter::eventLabel($event);
@endphp

<div class="space-y-3">
	@forelse($activities as $activity)
		@php
			$changes = $activity->changes;
			$newValues = $changes->get('attributes', []);
			$oldValues = $changes->get('old', []);
		@endphp
		<div class="rounded-2xl border border-slate-200 bg-white p-4">
			<div class="flex flex-wrap items-center justify-between gap-3">
				<div class="space-y-1">
					<div class="text-sm font-semibold text-slate-900">
						{{ $eventLabel($activity->event) }}
					</div>
					<div class="text-xs text-slate-500">
						{{ $activity->description }}
					</div>
					@if($activity->causer)
						<div class="text-xs text-slate-500">
							{{ __('common.user') }}: {{ $activity->causer?->name }}
						</div>
					@endif
				</div>
				<div class="text-xs text-slate-500">{{ $activity->created_at?->format('Y-m-d H:i') ?? '—' }}</div>
			</div>

			@if(is_array($newValues) && count($newValues) > 0)
				<div class="mt-3 space-y-2 text-sm">
					@foreach($newValues as $field => $newValue)
						<div class="flex flex-wrap items-center gap-2">
							<span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $labelFor((string) $field) }}</span>
							<span class="text-slate-500">{{ $formatValue($oldValues[$field] ?? null) }}</span>
							<span class="text-slate-400">→</span>
							<span class="text-slate-900">{{ $formatValue($newValue) }}</span>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	@empty
		<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
	@endforelse
</div>

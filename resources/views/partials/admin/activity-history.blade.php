@php
	$formatValue = fn ($value) => \App\Support\ActivityLogFormatter::formatValue($value);
	$labelFor = fn (string $field) => \App\Support\ActivityLogFormatter::labelFor($field);
	$valueMaps = $valueMaps ?? [];
@endphp

<div class="space-y-3">
	@forelse($activities as $activity)
		@php
			$changes = $activity->changes;
			$newValues = $changes->get('attributes', []);
			$oldValues = $changes->get('old', []);
			$summary = \App\Support\ActivityLogFormatter::summary($activity);
			$rawDescription = (string) $activity->description;
			$rawEvent = (string) $activity->event;
			$hideDescription = $rawDescription === '' || $rawDescription === $rawEvent || in_array($rawDescription, ['created', 'updated', 'deleted', 'attached', 'detached'], true);
			$hasDetails = !$hideDescription || (is_array($newValues) && count($newValues) > 0);
		@endphp
		<div class="rounded-2xl border border-slate-200 bg-white p-4">
			<div class="flex flex-wrap items-center justify-between gap-3">
				<div class="space-y-1">
					<div class="text-sm font-semibold text-slate-900">{{ $summary }}</div>
					@if($activity->causer)
						<div class="text-xs text-slate-500">{{ __('common.user') }}: {{ $activity->causer?->name }}</div>
					@endif
				</div>
				<div class="text-xs text-slate-500">{{ $activity->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
			</div>

			@if($hasDetails)
				<details class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2">
					<summary class="cursor-pointer text-xs font-semibold text-slate-600">{{ __('common.details') }}</summary>
					<div class="mt-2 space-y-2 text-sm">
						@if(!$hideDescription)
							<div class="text-xs text-slate-600">{{ $rawDescription }}</div>
						@endif
						@if(is_array($newValues) && count($newValues) > 0)
							@foreach($newValues as $field => $newValue)
								@php
									$fieldKey = (string) $field;
									$map = $valueMaps[$fieldKey] ?? null;
									$oldRaw = $oldValues[$field] ?? null;
									$newRaw = $newValue;
									if (is_array($map)) {
										$oldRaw = $map[$oldRaw] ?? $oldRaw;
										$newRaw = $map[$newRaw] ?? $newRaw;
									}
									$oldFormatted = $formatValue($oldRaw);
									$newFormatted = $formatValue($newRaw);
								@endphp
								<div class="flex flex-wrap items-center gap-2">
									<span class="rounded-lg bg-white px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $labelFor($fieldKey) }}</span>
									@if($oldFormatted !== '-')
										<span class="text-slate-500">{{ $oldFormatted }}</span>
									@endif
									<span class="text-slate-400">-></span>
									<span class="text-slate-900">{{ $newFormatted }}</span>
								</div>
							@endforeach
						@endif
					</div>
				</details>
			@endif
		</div>
	@empty
		<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
	@endforelse
</div>

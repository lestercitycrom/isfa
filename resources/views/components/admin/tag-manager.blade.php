@props([
	'selectedTags',
	'tagSuggestions',
	'showTagDropdown' => false,
])

<div class="space-y-3">
	@if ($selectedTags->isNotEmpty())
		<div class="flex flex-wrap gap-2">
			@foreach ($selectedTags as $tag)
				<span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">
					{{ $tag->name }}
					<button
						type="button"
						wire:click="removeTag({{ $tag->id }})"
						class="rounded text-slate-400 hover:text-slate-700"
						aria-label="remove tag"
					>
						&times;
					</button>
				</span>
			@endforeach
		</div>
	@endif

	<div class="relative">
		<input
			type="text"
			wire:model.live.debounce.250ms="tagInput"
			wire:keydown.enter.prevent="addTagFromInput"
			wire:focus="$set('showTagDropdown', true)"
			placeholder="{{ __('common.tag_input_placeholder') }}"
			class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
		/>

		@if($showTagDropdown)
			<div class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-lg">
				@forelse($tagSuggestions as $option)
					<button
						type="button"
						wire:click="selectTag({{ $option->id }})"
						class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-slate-50"
					>
						<span class="font-medium text-slate-900">{{ $option->name }}</span>
					</button>
				@empty
					<div class="px-3 py-2 text-sm text-slate-500">{{ __('common.tag_create_hint') }}</div>
				@endforelse
			</div>
		@endif
	</div>

	<div class="text-xs text-slate-500">{{ __('common.tag_input_hint') }}</div>
</div>

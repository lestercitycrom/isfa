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

	<div class="relative" x-data x-on:click.outside="$wire.set('showTagDropdown', false)">
		<input
			type="text"
			wire:model.live.debounce.250ms="tagInput"
			wire:keydown.enter.prevent="addTagFromInput"
			wire:focus="$set('showTagDropdown', true)"
			placeholder="{{ __('common.tag_input_placeholder') }}"
			class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200 focus:ring-offset-0"
		/>

		@if($showTagDropdown)
			<div class="absolute z-30 mt-2 max-h-60 w-full min-w-[12rem] overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl ring-1 ring-slate-900/5">
				@forelse($tagSuggestions as $option)
					<button
						type="button"
						wire:click="selectTag({{ $option->id }})"
						class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-100 focus:bg-slate-100 focus:outline-none"
					>
						<span class="font-medium text-slate-900">{{ $option->name }}</span>
					</button>
				@empty
					<div class="px-3 py-3 text-sm text-slate-500">{{ __('common.tag_create_hint') }}</div>
				@endforelse
			</div>
		@endif
	</div>

	<div class="text-xs text-slate-500">{{ __('common.tag_input_hint') }}</div>
</div>

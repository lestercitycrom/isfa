<div class="space-y-6">
	<x-admin.page-header
		:title="__('tenders.create.title')"
		:subtitle="__('tenders.create.subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.tenders.index')">
				{{ __('common.back') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.card>
		<form wire:submit="sync" class="space-y-6">
			<x-admin.input
				:label="__('common.event_id')"
				wire:model="eventId"
				:error="$errors->first('eventId')"
				:placeholder="__('tenders.create.placeholder')"
			/>

			<div class="flex items-center gap-3">
				<x-admin.button variant="primary" type="submit" :disabled="$isSyncing" class="min-w-[10.5rem] justify-center">
					<x-admin.icon name="download" class="h-4 w-4" />
					<span>
						@if($isSyncing)
							{{ __('tenders.actions.syncing') }}
						@else
							{{ __('tenders.actions.sync_tender') }}
						@endif
					</span>
				</x-admin.button>

			</div>

			@if($lastOutput)
				<div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
					<div class="text-xs font-semibold text-slate-600">{{ __('tenders.create.last_output') }}</div>
					<pre class="mt-2 text-xs text-slate-700 whitespace-pre-wrap">{{ $lastOutput }}</pre>
				</div>
			@endif
		</form>
	</x-admin.card>

	<x-admin.card :title="__('tenders.create.tip_title')">
		<div class="text-sm text-slate-700">
			{{ __('tenders.create.tip_text') }}
			<span class="font-mono">https://etender.gov.az/main/competition/detail/346012</span>
		</div>
	</x-admin.card>
</div>

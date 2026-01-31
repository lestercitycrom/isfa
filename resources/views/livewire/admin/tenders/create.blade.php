<div class="space-y-6">
	<x-admin.page-header
		title="Add tender"
		subtitle="Enter eTender event id (e.g. 346012) and run sync"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.tenders.index')">
				Back
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.card>
		<form wire:submit="sync" class="space-y-6">
			<x-admin.input
				label="Event ID"
				wire:model="eventId"
				:error="$errors->first('eventId')"
				placeholder="346012"
			/>

			<div class="flex items-center gap-3">
				<x-admin.button variant="primary" type="submit" :disabled="$isSyncing">
					<x-admin.icon name="download" class="h-4 w-4" />
					<span>
						@if($isSyncing)
							Syncing...
						@else
							Sync tender
						@endif
					</span>
				</x-admin.button>

				@if(session('status'))
					<span class="text-sm text-emerald-700">{{ session('status') }}</span>
				@endif
			</div>

			@if($lastOutput)
				<div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
					<div class="text-xs font-semibold text-slate-600">Last output</div>
					<pre class="mt-2 text-xs text-slate-700 whitespace-pre-wrap">{{ $lastOutput }}</pre>
				</div>
			@endif
		</form>
	</x-admin.card>

	<x-admin.card title="Tip">
		<div class="text-sm text-slate-700">
			You can take the id from URL like:
			<span class="font-mono">https://etender.gov.az/main/competition/detail/346012</span>
		</div>
	</x-admin.card>
</div>

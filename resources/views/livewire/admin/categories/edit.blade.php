<div class="space-y-6">
	<x-admin.page-header
		:title="$category ? __('common.editing_category') : __('common.creating_category')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.categories.index')">
				{{ __('common.back') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<div class="flex flex-wrap gap-2">
		<button
			type="button"
			wire:click="setTab('details')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'details' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.tab_details') }}
		</button>
		<button
			type="button"
			wire:click="setTab('history')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'history' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.tab_history') }}
		</button>
		<button
			type="button"
			wire:click="setTab('comments')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'comments' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.comments') }}
		</button>
	</div>

	@if($tab === 'details')
		<x-admin.card>
			<form wire:submit="save" class="space-y-6">
				@if ($isAdmin)
					<x-admin.select
						:label="__('common.company')"
						wire:model="company_id"
						:error="$errors->first('company_id')"
					>
						<option value="">{{ __('common.company_not_set') }}</option>
						@foreach ($companies as $company)
							<option value="{{ $company->id }}">{{ $company->company_name ?? $company->name }}</option>
						@endforeach
					</x-admin.select>
				@endif

				<x-admin.input
					:label="__('common.name')"
					type="text"
					wire:model="name"
					required
					autofocus
					:error="$errors->first('name')"
				/>

				<x-admin.input
					:label="__('common.description')"
					type="textarea"
					wire:model="description"
					:error="$errors->first('description')"
				/>

				<div class="flex items-center gap-4">
					<x-admin.button variant="primary" type="submit">
						{{ __('common.save') }}
					</x-admin.button>
					@if ($category)
						<x-admin.button variant="secondary" :href="route('admin.categories.index')">
							{{ __('common.cancel') }}
						</x-admin.button>
					@endif
				</div>
			</form>
		</x-admin.card>
	@endif

	@if($tab === 'history')
		<x-admin.card :title="__('common.activity_history')">
			@include('partials.admin.activity-history', ['activities' => $activities])
		</x-admin.card>
	@endif

	@if($tab === 'comments')
		<x-admin.card :title="__('common.comments')">
			<form wire:submit.prevent="saveComment" class="space-y-4">
				<x-admin.input
					:label="__('common.comment')"
					type="textarea"
					wire:model="comment"
				/>
				<x-admin.button variant="primary" type="submit">
					{{ __('common.save') }}
				</x-admin.button>
			</form>
		</x-admin.card>
	@endif
</div>

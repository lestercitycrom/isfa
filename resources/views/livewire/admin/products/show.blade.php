<div class="space-y-6">
	<x-admin.page-header
		:title="$product->name"
		:subtitle="__('common.product_suppliers_management')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.products.index')">
				{{ __('common.back') }}
			</x-admin.button>
			<x-admin.button variant="primary" :href="route('admin.products.edit', $product)">
				<x-admin.icon name="pencil" class="h-4 w-4" />
				{{ __('common.edit') }}
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
		@if($product->photo_path)
			<x-admin.card>
				<div class="flex items-center gap-4">
					<img
						src="{{ asset('storage/' . $product->photo_path) }}"
						alt="Photo"
						class="h-24 w-24 rounded-lg object-cover border border-slate-200"
					/>
					<div>
						<div class="text-xs font-semibold text-slate-600">{{ __('common.photo') }}</div>
						<div class="text-sm text-slate-900">{{ $product->name }}</div>
					</div>
				</div>
			</x-admin.card>
		@endif

		@if ($product->description)
			<x-admin.card>
				<div class="text-sm text-slate-700 whitespace-pre-line">{{ $product->description }}</div>
			</x-admin.card>
		@endif

		<x-admin.card :title="__('common.suppliers_for_product')">
			<div class="space-y-4">
				<x-admin.table :zebra="true">
					<x-slot name="head">
						<tr>
							<x-admin.th>{{ __('common.supplier') }}</x-admin.th>
							<x-admin.th>{{ __('common.status') }}</x-admin.th>
							<x-admin.th>{{ __('common.price_terms') }}</x-admin.th>
						</tr>
					</x-slot>

					@forelse ($product->suppliers as $s)
						<tr class="hover:bg-slate-50/70">
							<x-admin.td>
								<a class="font-medium text-slate-900 underline hover:text-slate-700" href="{{ route('admin.suppliers.show', $s) }}">
									{{ $s->name }}
								</a>
							</x-admin.td>

							<x-admin.td>
								@if(($s->pivot->status ?? null) === 'reserve')
									{{ __('common.status_reserve') }}
								@else
									{{ __('common.status_primary') }}
								@endif
							</x-admin.td>

							<x-admin.td>
								{{ $s->pivot->terms ?: '—' }}
							</x-admin.td>
						</tr>
					@empty
						<tr>
							<x-admin.td colspan="3" class="text-center py-8 text-slate-500">
								{{ __('common.no_linked_suppliers') }}
							</x-admin.td>
						</tr>
					@endforelse
				</x-admin.table>
			</div>
		</x-admin.card>
	@endif

	@if($tab === 'history')
		<x-admin.card :title="__('common.activity_history')">
			@include('partials.admin.activity-history', ['activities' => $activities, 'valueMaps' => ['category_id' => $categoryMap]])
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
				<div class="flex items-center gap-4">
					<x-admin.button variant="primary" type="submit">
						{{ __('common.save') }}
					</x-admin.button>
					<x-action-message class="text-sm text-slate-600" on="comment-saved">
						{{ __('common.saved') }}
					</x-action-message>
				</div>
			</form>
		</x-admin.card>
	@endif
</div>

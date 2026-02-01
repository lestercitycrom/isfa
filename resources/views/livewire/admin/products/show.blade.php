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
	</div>

	@if($tab === 'details')
		@if ($product->description)
			<x-admin.card>
				<div class="text-sm text-slate-700 whitespace-pre-line">{{ $product->description }}</div>
			</x-admin.card>
		@endif

		<x-admin.card :title="__('common.suppliers_for_product')">
			<div class="space-y-4">
				<div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
					<div class="lg:col-span-2">
						<x-admin.select
							:label="__('common.supplier')"
							wire:model="attachSupplierId"
							:error="$errors->first('attachSupplierId')"
						>
							<option value="0">—</option>
							@foreach ($suppliers as $s)
								<option value="{{ $s->id }}">{{ $s->name }}</option>
							@endforeach
						</x-admin.select>
					</div>

					<div>
						<x-admin.select
							:label="__('common.status')"
							wire:model="attachStatus"
							:error="$errors->first('attachStatus')"
						>
							<option value="primary">{{ __('common.status_primary') }}</option>
							<option value="reserve">{{ __('common.status_reserve') }}</option>
						</x-admin.select>
					</div>

					<div class="flex items-end">
						<x-admin.button variant="primary" wire:click="attach" class="w-full">
							{{ __('common.link') }}
						</x-admin.button>
					</div>

					<div class="lg:col-span-4">
						<x-admin.input
							:label="__('common.price_terms')"
							type="textarea"
							wire:model="attachTerms"
							:error="$errors->first('attachTerms')"
						/>
					</div>
				</div>

				<x-admin.table :zebra="true">
					<x-slot name="head">
						<tr>
							<x-admin.th>{{ __('common.supplier') }}</x-admin.th>
							<x-admin.th>{{ __('common.status') }}</x-admin.th>
							<x-admin.th>{{ __('common.price_terms') }}</x-admin.th>
							<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
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
								<x-admin.select wire:model="pivotStatus.{{ $s->id }}" size="sm">
									<option value="primary">{{ __('common.status_primary') }}</option>
									<option value="reserve">{{ __('common.status_reserve') }}</option>
								</x-admin.select>
							</x-admin.td>

							<x-admin.td>
								<x-admin.input
									type="textarea"
									wire:model="pivotTerms.{{ $s->id }}"
									size="sm"
								/>
							</x-admin.td>

							<x-admin.td align="right" nowrap>
								<div class="inline-flex items-center gap-2">
									<x-admin.button variant="secondary" size="sm" wire:click="savePivot({{ $s->id }})">
										{{ __('common.save') }}
									</x-admin.button>
									<x-admin.icon-button
										icon="trash"
										:title="__('common.detach')"
										variant="danger"
										wire:click="detach({{ $s->id }})"
										onclick="if(!confirm('{{ __('common.confirm_detach') }}')){event.preventDefault();event.stopImmediatePropagation();}"
									/>
								</div>
							</x-admin.td>
						</tr>
					@empty
						<tr>
							<x-admin.td colspan="4" class="text-center py-8 text-slate-500">
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
			@include('partials.admin.activity-history', ['activities' => $activities])
		</x-admin.card>
	@endif
</div>
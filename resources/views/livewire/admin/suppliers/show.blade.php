<div class="space-y-6">
	<x-admin.page-header
		title="{{ $supplier->name }}"
		:subtitle="__('common.products_for_supplier')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.suppliers.index')">
				{{ __('common.back') }}
			</x-admin.button>
			<x-admin.button variant="primary" :href="route('admin.suppliers.edit', $supplier)">
				<x-admin.icon name="pencil" class="h-4 w-4" />
				{{ __('common.edit') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	@if ($supplier->contact_name || $supplier->phone || $supplier->email || $supplier->website)
		<x-admin.card>
			<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
				@if($supplier->contact_name)
					<div>
						<div class="text-xs font-semibold text-slate-600">{{ __('common.contact_name') }}</div>
						<div class="mt-1 text-sm text-slate-900">{{ $supplier->contact_name }}</div>
					</div>
				@endif
				@if($supplier->phone)
					<div>
						<div class="text-xs font-semibold text-slate-600">{{ __('common.phone') }}</div>
						<div class="mt-1 text-sm text-slate-900">{{ $supplier->phone }}</div>
					</div>
				@endif
				@if($supplier->email)
					<div>
						<div class="text-xs font-semibold text-slate-600">{{ __('common.email') }}</div>
						<div class="mt-1 text-sm text-slate-900">{{ $supplier->email }}</div>
					</div>
				@endif
				@if($supplier->website)
					<div>
						<div class="text-xs font-semibold text-slate-600">{{ __('common.website') }}</div>
						<div class="mt-1 text-sm text-slate-900">
							<a href="{{ $supplier->website }}" target="_blank" class="text-slate-600 hover:text-slate-900 underline">
								{{ $supplier->website }}
							</a>
						</div>
					</div>
				@endif
			</div>
		</x-admin.card>
	@endif

	@if ($supplier->comment)
		<x-admin.card>
			<div class="text-sm text-slate-700 whitespace-pre-line">{{ $supplier->comment }}</div>
		</x-admin.card>
	@endif

	<x-admin.card :title="__('common.products_for_supplier')">
		<x-admin.table :zebra="true">
			<x-slot name="head">
				<tr>
					<x-admin.th>{{ __('common.products') }}</x-admin.th>
					<x-admin.th>{{ __('common.category') }}</x-admin.th>
					<x-admin.th>{{ __('common.status') }}</x-admin.th>
					<x-admin.th>{{ __('common.price_terms') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($supplier->products as $p)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td>
						<a class="font-medium text-slate-900 underline hover:text-slate-700" href="{{ route('admin.products.show', $p) }}">
							{{ $p->name }}
						</a>
					</x-admin.td>
					<x-admin.td>
						@if($p->category)
							<x-admin.badge variant="blue">{{ $p->category->name }}</x-admin.badge>
						@else
							<span class="text-slate-400">—</span>
						@endif
					</x-admin.td>
					<x-admin.td>
						<x-admin.status-badge :status="$p->pivot->status->value" />
					</x-admin.td>
					<x-admin.td class="text-sm text-slate-600 whitespace-pre-line">
						{{ $p->pivot->terms }}
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="4" class="text-center py-8 text-slate-500">
						{{ __('common.no_products') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>
	</x-admin.card>
</div>

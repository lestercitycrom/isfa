<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.companies')"
		:subtitle="__('common.companies_subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="primary" :href="route('admin.companies.create')">
				<x-admin.icon name="plus" class="h-4 w-4" />
				{{ __('common.add_company') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.filters-bar>
		<div class="lg:col-span-4">
			<x-admin.filter-input
				wire:model.live="search"
				:placeholder="__('common.search_company')"
				icon="search"
			/>
		</div>
	</x-admin.filters-bar>

	<x-admin.card>
		<x-admin.table :zebra="true" :sticky="true">
			<x-slot name="head">
				<tr>
					<x-admin.th>{{ __('common.company') }}</x-admin.th>
					<x-admin.th>{{ __('common.contacts') }}</x-admin.th>
					<x-admin.th>{{ __('common.accounts') }}</x-admin.th>
					<x-admin.th>{{ __('common.tax_id') }}</x-admin.th>
					<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($companies as $company)
				@php($primaryUser = $company->users->first())
				<tr class="hover:bg-slate-50/70">
					<x-admin.td>
						<div class="font-medium text-slate-900">{{ $company->name }}</div>
						@if($company->legal_name)
							<div class="text-xs text-slate-500">{{ $company->legal_name }}</div>
						@endif
					</x-admin.td>
					<x-admin.td>
						@if($company->contact_name)
							<div class="text-slate-900">{{ $company->contact_name }}</div>
						@endif
						<div class="mt-1 text-xs text-slate-500">
							@if($primaryUser?->email){{ $primaryUser->email }}@endif
							@if($primaryUser?->email && $company->phone) • @endif
							@if($company->phone){{ $company->phone }}@endif
						</div>
					</x-admin.td>
					<x-admin.td>
						{{ $company->users_count }}
					</x-admin.td>
					<x-admin.td>
						{{ $company->tax_id ?: '—' }}
					</x-admin.td>
					<x-admin.td align="right" nowrap>
						<x-admin.table-actions
							:viewHref="route('admin.companies.show', $company)"
							:editHref="route('admin.companies.edit', $company)"
						>
							<x-admin.icon-button
								icon="trash"
								:title="__('common.delete')"
								variant="danger"
								wire:click="delete({{ $company->id }})"
								onclick="if(!confirm('{{ __('common.confirm_delete_company', ['name' => $company->name]) }}')){event.preventDefault();event.stopImmediatePropagation();}"
							/>
						</x-admin.table-actions>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="5" class="text-center py-8 text-slate-500">
						{{ __('common.no_companies') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		<div class="mt-4">
			{{ $companies->links('pagination.admin') }}
		</div>
	</x-admin.card>
</div>

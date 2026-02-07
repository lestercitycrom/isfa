<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.categories_title')"
		:subtitle="__('common.categories_subtitle')"
	>
		<x-slot name="actions">
			<a class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ route('admin.export.categories.excel') }}">
				<x-admin.icon name="file-text" class="h-4 w-4" />
				{{ __('common.export_excel') }}
			</a>

			@if(\Illuminate\Support\Facades\Route::has('admin.categories.create'))
				<x-admin.button variant="primary" :href="route('admin.categories.create')">
					<x-admin.icon name="plus" class="h-4 w-4" />
					{{ __('common.new_category') }}
				</x-admin.button>
			@endif
		</x-slot>
	</x-admin.page-header>

	<x-admin.filters-bar>
		<div class="lg:col-span-4">
			<x-admin.filter-input
				wire:model.live="search"
				:placeholder="__('common.search_by_name')"
				icon="search"
			/>
		</div>
		@if ($isAdmin)
			<div class="lg:col-span-3">
				<x-admin.filter-select
					wire:model.live="companyFilter"
					label="{{ __('common.company') }}"
					icon="building"
				>
					<option value="">{{ __('common.all_companies') }}</option>
					<option value="0">{{ __('common.admin') }}</option>
					@foreach ($companies as $company)
						<option value="{{ $company->id }}">{{ $company->company_name ?? $company->name }}</option>
					@endforeach
				</x-admin.filter-select>
			</div>
		@endif
	</x-admin.filters-bar>

	<x-admin.card>
		<x-admin.table :zebra="true" :sticky="true">
			<x-slot name="head">
				<tr>
					<x-admin.th>{{ __('common.name') }}</x-admin.th>
					@if ($isAdmin)
						<x-admin.th>{{ __('common.company') }}</x-admin.th>
					@endif
					<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($categories as $cat)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td>
						<div class="font-medium text-slate-900">{{ $cat->name }}</div>
						@if($cat->description)
							<div class="mt-1 text-xs text-slate-500 line-clamp-1">{{ $cat->description }}</div>
						@endif
					</x-admin.td>
					@if ($isAdmin)
						<x-admin.td>
							{{ $cat->company?->company_name ?? $cat->company?->name ?? '—' }}
						</x-admin.td>
					@endif
					<x-admin.td align="right" nowrap>
						<div class="inline-flex items-center gap-2">
							<x-admin.icon-button :href="route('admin.categories.edit', $cat)" icon="pencil" :title="__('common.edit')" variant="secondary" />
							<x-admin.icon-button wire:click="delete({{ $cat->id }})" onclick="return confirm('{{ __('common.confirm_delete_category') }}')" icon="trash" :title="__('common.delete')" variant="danger" />
						</div>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="{{ $isAdmin ? 3 : 2 }}" class="text-center py-8 text-slate-500">
						{{ __('common.no_categories') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		<div class="mt-4">
			{{ $categories->links('pagination.admin') }}
		</div>
	</x-admin.card>

	@if(false)
		<!-- Danger Zone -->
		<div class="rounded-2xl border-2 border-red-200 bg-red-50 p-6">
			<div class="flex items-start justify-between gap-4">
				<div class="flex-1">
					<h3 class="text-lg font-semibold text-red-900">{{ __('common.danger_zone') }}</h3>
					<p class="mt-1 text-sm text-red-700">
						{{ __('common.delete_all_categories_warning') }}
					</p>
				</div>
				<x-admin.button
					variant="danger"
					size="md"
					wire:click="deleteAllCategories"
					onclick="if(!confirm('{{ __('common.confirm_delete_all_categories') }}')){event.preventDefault();event.stopImmediatePropagation();}"
				>
					{{ __('common.delete_all_categories') }}
				</x-admin.button>
			</div>
		</div>
	@endif
</div>

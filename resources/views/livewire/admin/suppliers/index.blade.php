<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.suppliers')"
		:subtitle="__('common.suppliers_subtitle')"
	>
		<x-slot name="actions">
			<a class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ route('admin.export.suppliers') }}">
				<x-admin.icon name="upload" class="h-4 w-4" />
				{{ __('common.export_csv') }}
			</a>
			<a class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ route('admin.export.suppliers.excel') }}">
				<x-admin.icon name="file-text" class="h-4 w-4" />
				{{ __('common.export_excel') }}
			</a>

			<form method="POST" action="{{ route('admin.import.suppliers') }}" enctype="multipart/form-data" class="inline-flex">
				@csrf
				<input id="suppliersImportFile" name="file" type="file" accept=".csv,.txt" class="hidden"
					onchange="this.form.submit()">

				<label for="suppliersImportFile" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition">
					<x-admin.icon name="download" class="h-4 w-4" />
					{{ __('common.import_csv') }}
				</label>
			</form>

			<x-admin.button variant="primary" :href="route('admin.suppliers.create')">
				<x-admin.icon name="plus" class="h-4 w-4" />
				{{ __('common.add_supplier') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.filters-bar>
		<div class="lg:col-span-4">
			<x-admin.filter-input
				wire:model.live="search"
				:placeholder="__('common.search_supplier')"
				icon="search"
			/>
		</div>
		@if ($isAdmin)
			<div class="lg:col-span-2">
				<x-admin.filter-select
					wire:model.live="companyFilter"
					:placeholder="__('common.all_companies')"
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
					<x-admin.th>{{ __('common.contacts') }}</x-admin.th>
					<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($suppliers as $supplier)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td>
						<div class="flex items-center gap-3">
							<div class="h-10 w-10 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center">
								@if ($supplier->photo_path)
									<img src="{{ asset('storage/' . $supplier->photo_path) }}" alt="{{ __('common.photo') }}" class="h-full w-full object-cover" />
								@else
									<x-admin.icon name="image" class="h-4 w-4 text-slate-400" />
								@endif
							</div>
							<div class="font-medium text-slate-900">{{ $supplier->name }}</div>
						</div>
					</x-admin.td>
					@if ($isAdmin)
						<x-admin.td>
							{{ $supplier->company?->company_name ?? $supplier->company?->name ?? '—' }}
						</x-admin.td>
					@endif
					<x-admin.td>
						@if($supplier->contact_name)
							<div class="text-slate-900">{{ $supplier->contact_name }}</div>
						@endif
						@if($supplier->phone || $supplier->email)
							<div class="mt-1 text-xs text-slate-500">
								@if($supplier->phone){{ $supplier->phone }}@endif
								@if($supplier->phone && $supplier->email) • @endif
								@if($supplier->email){{ $supplier->email }}@endif
							</div>
						@endif
					</x-admin.td>
					<x-admin.td align="right" nowrap>
						<x-admin.table-actions
							:viewHref="route('admin.suppliers.show', $supplier)"
							:editHref="route('admin.suppliers.edit', $supplier)"
						>
							<x-admin.icon-button
								icon="trash"
								:title="__('common.delete')"
								variant="danger"
								wire:click="delete({{ $supplier->id }})"
								onclick="if(!confirm('{{ __('common.confirm_delete_supplier', ['name' => $supplier->name]) }}')){event.preventDefault();event.stopImmediatePropagation();}"
							/>
						</x-admin.table-actions>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="{{ $isAdmin ? 4 : 3 }}" class="text-center py-8 text-slate-500">
						{{ __('common.no_suppliers') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		<div class="mt-4">
			{{ $suppliers->links('pagination.admin') }}
		</div>
	</x-admin.card>

	@if(false)
		<!-- Danger Zone -->
		<div class="rounded-2xl border-2 border-red-200 bg-red-50 p-6">
			<div class="flex items-start justify-between gap-4">
				<div class="flex-1">
					<h3 class="text-lg font-semibold text-red-900">{{ __('common.danger_zone') }}</h3>
					<p class="mt-1 text-sm text-red-700">
						{{ __('common.delete_all_suppliers_warning') }}
					</p>
				</div>
				<x-admin.button
					variant="danger"
					size="md"
					wire:click="deleteAllSuppliers"
					onclick="if(!confirm('{{ __('common.confirm_delete_all_suppliers') }}')){event.preventDefault();event.stopImmediatePropagation();}"
				>
					{{ __('common.delete_all_suppliers') }}
				</x-admin.button>
			</div>
		</div>
	@endif
</div>

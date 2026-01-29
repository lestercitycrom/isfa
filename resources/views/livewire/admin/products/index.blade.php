<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.products')"
		:subtitle="__('common.products_subtitle')"
	>
		<x-slot name="actions">
			<a class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ route('admin.export.products') }}">
				<x-admin.icon name="upload" class="h-4 w-4" />
				{{ __('common.export_csv') }}
			</a>

			<form method="POST" action="{{ route('admin.import.products') }}" enctype="multipart/form-data" class="inline-flex">
				@csrf
				<input id="productsImportFile" name="file" type="file" accept=".csv,.txt" class="hidden"
					onchange="this.form.submit()">

				<label for="productsImportFile" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition">
					<x-admin.icon name="download" class="h-4 w-4" />
					{{ __('common.import_csv') }}
				</label>
			</form>

			<x-admin.button variant="primary" :href="route('admin.products.create')">
				<x-admin.icon name="plus" class="h-4 w-4" />
				{{ __('common.add_product') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.filters-bar>
		<div class="lg:col-span-4">
			<x-admin.filter-input
				wire:model.live="search"
				:placeholder="__('common.search_product')"
				icon="search"
			/>
		</div>
		<div class="lg:col-span-3">
			<x-admin.filter-select
				wire:model.live="categoryFilter"
				label="{{ __('common.category') }}"
				icon="database"
			>
				<option value="">{{ __('common.all_categories') }}</option>
				@foreach ($categories as $cat)
					<option value="{{ $cat->id }}">{{ $cat->name }}</option>
				@endforeach
			</x-admin.filter-select>
		</div>
		<div class="lg:col-span-3">
			<x-admin.filter-select
				wire:model.live="supplierFilter"
				label="{{ __('common.suppliers') }}"
				icon="users"
			>
				<option value="">{{ __('common.all_suppliers') }}</option>
				@foreach ($suppliers as $supplier)
					<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
				@endforeach
			</x-admin.filter-select>
		</div>
		<div class="lg:col-span-2">
			<x-admin.button variant="secondary" wire:click="$set('categoryFilter', null); $set('supplierFilter', null); $set('search', '')">
				{{ __('common.reset') }}
			</x-admin.button>
		</div>
	</x-admin.filters-bar>

	<x-admin.card>
		<x-admin.table :zebra="true" :sticky="true">
			<x-slot name="head">
				<tr>
					<x-admin.th>{{ __('common.name') }}</x-admin.th>
					<x-admin.th>{{ __('common.category') }}</x-admin.th>
					<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($products as $product)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td>
						<div class="font-medium text-slate-900">{{ $product->name }}</div>
						@if($product->description)
							<div class="mt-1 text-xs text-slate-500 line-clamp-1">{{ $product->description }}</div>
						@endif
					</x-admin.td>
					<x-admin.td>
						@if($product->category)
							<x-admin.badge variant="blue">{{ $product->category->name }}</x-admin.badge>
						@else
							<span class="text-slate-400">—</span>
						@endif
					</x-admin.td>
					<x-admin.td align="right" nowrap>
						<x-admin.table-actions
							:viewHref="route('admin.products.show', $product)"
							:editHref="route('admin.products.edit', $product)"
						>
							<x-admin.icon-button
								icon="trash"
								:title="__('common.delete')"
								variant="danger"
								wire:click="delete({{ $product->id }})"
								onclick="if(!confirm('{{ __('common.confirm_delete_product', ['name' => $product->name]) }}')){event.preventDefault();event.stopImmediatePropagation();}"
							/>
						</x-admin.table-actions>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="3" class="text-center py-8 text-slate-500">
						{{ __('common.no_products') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		<div class="mt-4">
			{{ $products->links('pagination.admin') }}
		</div>
	</x-admin.card>

	<!-- Danger Zone -->
	<div class="rounded-2xl border-2 border-red-200 bg-red-50 p-6">
		<div class="flex items-start justify-between gap-4">
			<div class="flex-1">
				<h3 class="text-lg font-semibold text-red-900">{{ __('common.danger_zone') }}</h3>
				<p class="mt-1 text-sm text-red-700">
					{{ __('common.delete_all_products_warning') }}
				</p>
			</div>
			<x-admin.button
				variant="danger"
				size="md"
				wire:click="deleteAllProducts"
				onclick="if(!confirm('{{ __('common.confirm_delete_all_products') }}')){event.preventDefault();event.stopImmediatePropagation();}"
			>
				{{ __('common.delete_all_products') }}
			</x-admin.button>
		</div>
	</div>
</div>

<div class="space-y-6">
	<x-admin.page-header
		title="Товары"
		subtitle="Список всех товаров с возможностью поиска и фильтрации."
	>
		<x-slot name="actions">
			<x-admin.button variant="primary" :href="route('admin.products.create')">
				<x-admin.icon name="plus" class="h-4 w-4" />
				Добавить товар
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.filters-bar>
		<div class="lg:col-span-4">
			<x-admin.filter-input
				wire:model.live="search"
				placeholder="Поиск товара..."
				icon="search"
			/>
		</div>
	</x-admin.filters-bar>

	<x-admin.card>
		<x-admin.table :zebra="true" :sticky="true">
			<x-slot name="head">
				<tr>
					<x-admin.th>Название</x-admin.th>
					<x-admin.th>Категория</x-admin.th>
					<x-admin.th align="right" nowrap>Действия</x-admin.th>
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
						/>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="3" class="text-center py-8 text-slate-500">
						Товары не найдены
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		<div class="mt-4">
			{{ $products->links('pagination.admin') }}
		</div>
	</x-admin.card>
</div>

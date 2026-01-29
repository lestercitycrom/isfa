<div class="space-y-6">
	<div class="flex items-center justify-between gap-4">
		<h1 class="text-2xl font-semibold">Товары</h1>

		<div class="flex items-center gap-2">
			<div class="w-80">
				<input wire:model.live="search" class="w-full rounded border px-3 py-2" placeholder="Поиск товара...">
			</div>

			<a class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800" href="{{ route('admin.products.create') }}">
				+ Добавить
			</a>
		</div>
	</div>

	<div class="rounded border bg-white">
		<table class="w-full text-sm">
			<thead>
			<tr class="border-b bg-zinc-50 text-left">
				<th class="p-3">Название</th>
				<th class="p-3">Категория</th>
				<th class="p-3 w-56"></th>
			</tr>
			</thead>
			<tbody>
			@foreach ($products as $product)
				<tr class="border-b">
					<td class="p-3">{{ $product->name }}</td>
					<td class="p-3">{{ $product->category?->name }}</td>
					<td class="p-3 text-right space-x-2">
						<a class="rounded border px-2 py-1 hover:bg-zinc-50" href="{{ route('admin.products.show', $product) }}">Open</a>
						<a class="rounded border px-2 py-1 hover:bg-zinc-50" href="{{ route('admin.products.edit', $product) }}">Edit</a>
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>

		<div class="p-3">
			{{ $products->links() }}
		</div>
	</div>
</div>

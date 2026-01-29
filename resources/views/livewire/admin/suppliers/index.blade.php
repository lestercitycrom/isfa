<div class="space-y-6">
	<div class="flex items-center justify-between gap-4">
		<h1 class="text-2xl font-semibold">Поставщики</h1>

		<div class="flex items-center gap-2">
			<div class="w-80">
				<input wire:model.live="search" class="w-full rounded border px-3 py-2" placeholder="Поиск поставщика...">
			</div>

			<a class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800" href="{{ route('admin.suppliers.create') }}">
				+ Добавить
			</a>
		</div>
	</div>

	<div class="rounded border bg-white">
		<table class="w-full text-sm">
			<thead>
			<tr class="border-b bg-zinc-50 text-left">
				<th class="p-3">Название</th>
				<th class="p-3">Контакты</th>
				<th class="p-3 w-56"></th>
			</tr>
			</thead>
			<tbody>
			@foreach ($suppliers as $supplier)
				<tr class="border-b">
					<td class="p-3">{{ $supplier->name }}</td>
					<td class="p-3 text-zinc-700">
						<div>{{ $supplier->contact_name }}</div>
						<div class="text-xs text-zinc-600">{{ $supplier->phone }} {{ $supplier->email }}</div>
					</td>
					<td class="p-3 text-right space-x-2">
						<a class="rounded border px-2 py-1 hover:bg-zinc-50" href="{{ route('admin.suppliers.show', $supplier) }}">Open</a>
						<a class="rounded border px-2 py-1 hover:bg-zinc-50" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a>
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>

		<div class="p-3">
			{{ $suppliers->links() }}
		</div>
	</div>
</div>

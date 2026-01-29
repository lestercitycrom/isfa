<div class="space-y-6">
	<div class="flex items-center justify-between gap-4">
		<div>
			<h1 class="text-2xl font-semibold">{{ $supplier->name }}</h1>
			<div class="text-sm text-zinc-600">
				{{ $supplier->contact_name }} · {{ $supplier->phone }} · {{ $supplier->email }}
			</div>
		</div>

		<div class="flex gap-2">
			<a class="rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.suppliers.index') }}">← Назад</a>
			<a class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a>
		</div>
	</div>

	@if ($supplier->comment)
		<div class="rounded border bg-white p-4">
			<div class="text-sm text-zinc-700 whitespace-pre-line">{{ $supplier->comment }}</div>
		</div>
	@endif

	<div class="rounded border bg-white p-4 space-y-3">
		<div class="text-lg font-semibold">Товары у этого поставщика</div>

		<div class="overflow-x-auto">
			<table class="w-full text-sm">
				<thead>
				<tr class="border-b bg-zinc-50 text-left">
					<th class="p-2">Товар</th>
					<th class="p-2">Категория</th>
					<th class="p-2 w-40">Статус</th>
					<th class="p-2">Условия</th>
				</tr>
				</thead>
				<tbody>
				@forelse ($supplier->products as $p)
					<tr class="border-b align-top">
						<td class="p-2">
							<a class="underline" href="{{ route('admin.products.show', $p) }}">{{ $p->name }}</a>
						</td>
						<td class="p-2">{{ $p->category?->name }}</td>
						<td class="p-2">{{ $p->pivot->status->label() }}</td>
						<td class="p-2 whitespace-pre-line">{{ $p->pivot->terms }}</td>
					</tr>
				@empty
					<tr>
						<td class="p-3 text-zinc-600" colspan="4">Нет товаров.</td>
					</tr>
				@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>

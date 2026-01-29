<div class="space-y-6">
	<div class="flex items-center justify-between gap-4">
		<div>
			<h1 class="text-2xl font-semibold">{{ $product->name }}</h1>
			<div class="text-sm text-zinc-600">
				Категория: {{ $product->category?->name ?? '—' }}
			</div>
		</div>

		<div class="flex gap-2">
			<a class="rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.products.index') }}">← Назад</a>
			<a class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800" href="{{ route('admin.products.edit', $product) }}">Edit</a>
		</div>
	</div>

	@if ($product->description)
		<div class="rounded border bg-white p-4">
			<div class="text-sm text-zinc-700 whitespace-pre-line">{{ $product->description }}</div>
		</div>
	@endif

	<div class="rounded border bg-white p-4 space-y-4">
		<div class="text-lg font-semibold">Поставщики по этому товару</div>

		<div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
			<div class="lg:col-span-2">
				<label class="mb-1 block text-sm font-medium">Поставщик</label>
				<select wire:model="attachSupplierId" class="w-full rounded border px-3 py-2">
					<option value="0">—</option>
					@foreach ($suppliers as $s)
						<option value="{{ $s->id }}">{{ $s->name }}</option>
					@endforeach
				</select>
				@error('attachSupplierId') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
			</div>

			<div>
				<label class="mb-1 block text-sm font-medium">Статус</label>
				<select wire:model="attachStatus" class="w-full rounded border px-3 py-2">
					<option value="primary">Основной</option>
					<option value="reserve">Резервный</option>
				</select>
				@error('attachStatus') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
			</div>

			<div class="flex items-end">
				<button wire:click="attach" class="w-full rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800">
					Привязать
				</button>
			</div>

			<div class="lg:col-span-4">
				<label class="mb-1 block text-sm font-medium">Цена / условия (текстом)</label>
				<textarea wire:model="attachTerms" class="w-full rounded border px-3 py-2" rows="3"></textarea>
				@error('attachTerms') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
			</div>
		</div>

		<div class="overflow-x-auto">
			<table class="w-full text-sm">
				<thead>
				<tr class="border-b bg-zinc-50 text-left">
					<th class="p-2">Поставщик</th>
					<th class="p-2 w-40">Статус</th>
					<th class="p-2">Условия</th>
					<th class="p-2 w-40"></th>
				</tr>
				</thead>
				<tbody>
				@forelse ($product->suppliers as $s)
					<tr class="border-b align-top">
						<td class="p-2">
							<a class="underline" href="{{ route('admin.suppliers.show', $s) }}">{{ $s->name }}</a>
						</td>

						<td class="p-2">
							<select wire:model="pivotStatus.{{ $s->id }}" class="w-full rounded border px-2 py-1">
								<option value="primary">Основной</option>
								<option value="reserve">Резервный</option>
							</select>
						</td>

						<td class="p-2">
							<textarea wire:model="pivotTerms.{{ $s->id }}" class="w-full rounded border px-2 py-1" rows="2"></textarea>
						</td>

						<td class="p-2 text-right space-x-2">
							<button wire:click="savePivot({{ $s->id }})" class="rounded border px-2 py-1 hover:bg-zinc-50">Save</button>
							<button wire:click="detach({{ $s->id }})" class="rounded border px-2 py-1 hover:bg-zinc-50"
								onclick="return confirm('Отвязать поставщика?')">Del</button>
						</td>
					</tr>
				@empty
					<tr>
						<td class="p-3 text-zinc-600" colspan="4">Нет привязанных поставщиков.</td>
					</tr>
				@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>

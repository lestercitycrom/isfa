<div class="space-y-6">
	<div class="flex items-center justify-between">
		<h1 class="text-2xl font-semibold">
			{{ $product ? 'Редактирование товара' : 'Создание товара' }}
		</h1>

		<a class="rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.products.index') }}">← Назад</a>
	</div>

	<div class="rounded border bg-white p-4 space-y-4 max-w-3xl">
		<div>
			<label class="mb-1 block text-sm font-medium">Категория</label>
			<select wire:model="category_id" class="w-full rounded border px-3 py-2">
				<option value="">—</option>
				@foreach ($categories as $cat)
					<option value="{{ $cat->id }}">{{ $cat->name }}</option>
				@endforeach
			</select>
			@error('category_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
		</div>

		<div>
			<label class="mb-1 block text-sm font-medium">Название</label>
			<input wire:model="name" class="w-full rounded border px-3 py-2">
			@error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
		</div>

		<div>
			<label class="mb-1 block text-sm font-medium">Описание</label>
			<textarea wire:model="description" class="w-full rounded border px-3 py-2" rows="6"></textarea>
			@error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
		</div>

		<div class="flex gap-2">
			<button wire:click="save" class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800">
				Сохранить
			</button>
			@if ($product)
				<a class="rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.products.show', $product) }}">
					Отмена
				</a>
			@endif
		</div>
	</div>
</div>

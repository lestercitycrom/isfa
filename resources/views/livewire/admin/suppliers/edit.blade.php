<div class="space-y-6">
	<div class="flex items-center justify-between">
		<h1 class="text-2xl font-semibold">
			{{ $supplier ? 'Редактирование поставщика' : 'Создание поставщика' }}
		</h1>

		<a class="rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.suppliers.index') }}">← Назад</a>
	</div>

	<div class="rounded border bg-white p-4 space-y-4 max-w-3xl">
		<div>
			<label class="mb-1 block text-sm font-medium">Название</label>
			<input wire:model="name" class="w-full rounded border px-3 py-2">
			@error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
		</div>

		<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
			<div>
				<label class="mb-1 block text-sm font-medium">Контактное лицо</label>
				<input wire:model="contact_name" class="w-full rounded border px-3 py-2">
			</div>

			<div>
				<label class="mb-1 block text-sm font-medium">Телефон</label>
				<input wire:model="phone" class="w-full rounded border px-3 py-2">
			</div>

			<div>
				<label class="mb-1 block text-sm font-medium">Email</label>
				<input wire:model="email" class="w-full rounded border px-3 py-2">
			</div>

			<div>
				<label class="mb-1 block text-sm font-medium">Сайт</label>
				<input wire:model="website" class="w-full rounded border px-3 py-2">
			</div>
		</div>

		<div>
			<label class="mb-1 block text-sm font-medium">Комментарий</label>
			<textarea wire:model="comment" class="w-full rounded border px-3 py-2" rows="5"></textarea>
		</div>

		<div class="flex gap-2">
			<button wire:click="save" class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800">
				Сохранить
			</button>
			@if ($supplier)
				<a class="rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.suppliers.show', $supplier) }}">
					Отмена
				</a>
			@endif
		</div>
	</div>
</div>

<div class="space-y-6">
	<div class="flex items-center justify-between gap-4">
		<h1 class="text-2xl font-semibold">Категории</h1>

		<div class="w-80">
			<input wire:model.live="search" class="w-full rounded border px-3 py-2" placeholder="Поиск по названию...">
		</div>
	</div>

	<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
		<div class="rounded border bg-white p-4">
			<div class="mb-3 flex items-center justify-between">
				<div class="font-semibold">Список</div>
				<button wire:click="startCreate" class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800">
					+ Новая
				</button>
			</div>

			<div class="overflow-x-auto">
				<table class="w-full text-sm">
					<thead>
					<tr class="border-b bg-zinc-50 text-left">
						<th class="p-2">Название</th>
						<th class="p-2 w-40"></th>
					</tr>
					</thead>
					<tbody>
					@foreach ($categories as $cat)
						<tr class="border-b">
							<td class="p-2">{{ $cat->name }}</td>
							<td class="p-2 text-right space-x-2">
								<button wire:click="startEdit({{ $cat->id }})" class="rounded border px-2 py-1 hover:bg-zinc-50">Edit</button>
								<button wire:click="delete({{ $cat->id }})" class="rounded border px-2 py-1 hover:bg-zinc-50"
									onclick="return confirm('Удалить категорию?')">Del</button>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>

			<div class="mt-3">
				{{ $categories->links() }}
			</div>
		</div>

		<div class="rounded border bg-white p-4">
			<div class="mb-3 font-semibold">
				{{ $editingId ? 'Редактирование' : 'Создание' }}
			</div>

			<div class="space-y-3">
				<div>
					<label class="mb-1 block text-sm font-medium">Название</label>
					<input wire:model="name" class="w-full rounded border px-3 py-2">
					@error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="mb-1 block text-sm font-medium">Описание</label>
					<textarea wire:model="description" class="w-full rounded border px-3 py-2" rows="5"></textarea>
					@error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
				</div>

				<div class="flex gap-2">
					<button wire:click="save" class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800">
						Сохранить
					</button>
					@if ($editingId)
						<button wire:click="startCreate" class="rounded border px-3 py-2 hover:bg-zinc-50">
							Отмена
						</button>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

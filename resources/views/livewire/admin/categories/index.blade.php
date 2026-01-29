<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.categories_title')"
		:subtitle="__('common.categories_subtitle')"
	>
		<x-slot name="actions">
			<a class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ route('admin.export.categories') }}">
				<x-admin.icon name="upload" class="h-4 w-4" />
				{{ __('common.export_csv') }}
			</a>

			<form method="POST" action="{{ route('admin.import.categories') }}" enctype="multipart/form-data" class="inline-flex">
				@csrf
				<input id="categoriesImportFile" name="file" type="file" accept=".csv,.txt" class="hidden"
					onchange="this.form.submit()">

				<label for="categoriesImportFile" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition">
					<x-admin.icon name="download" class="h-4 w-4" />
					{{ __('common.import_csv') }}
				</label>
			</form>

			<x-admin.button variant="primary" wire:click="startCreate">
				<x-admin.icon name="plus" class="h-4 w-4" />
				{{ __('common.new_category') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.filters-bar>
		<div class="lg:col-span-4">
			<x-admin.filter-input
				wire:model.live="search"
				:placeholder="__('common.search_by_name')"
				icon="search"
			/>
		</div>
	</x-admin.filters-bar>

	<x-admin.card>
		<x-admin.table :zebra="true" :sticky="true">
			<x-slot name="head">
				<tr>
					<x-admin.th>{{ __('common.name') }}</x-admin.th>
					<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($categories as $cat)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td>
						<div class="font-medium text-slate-900">{{ $cat->name }}</div>
						@if($cat->description)
							<div class="mt-1 text-xs text-slate-500 line-clamp-1">{{ $cat->description }}</div>
						@endif
					</x-admin.td>
					<x-admin.td align="right" nowrap>
						<div class="inline-flex items-center gap-2">
							<x-admin.icon-button wire:click="startEdit({{ $cat->id }})" icon="pencil" :title="__('common.edit')" variant="secondary" />
							<x-admin.icon-button wire:click="delete({{ $cat->id }})" onclick="return confirm('{{ __('common.confirm_delete_category') }}')" icon="trash" :title="__('common.delete')" variant="danger" />
						</div>
					</x-admin.td>
				</tr>
			@empty
				<tr>
					<x-admin.td colspan="2" class="text-center py-8 text-slate-500">
						{{ __('common.no_categories') }}
					</x-admin.td>
				</tr>
			@endforelse
		</x-admin.table>

		<div class="mt-4">
			{{ $categories->links('pagination.admin') }}
		</div>
	</x-admin.card>

	<!-- Danger Zone -->
	<div class="rounded-2xl border-2 border-red-200 bg-red-50 p-6">
		<div class="flex items-start justify-between gap-4">
			<div class="flex-1">
				<h3 class="text-lg font-semibold text-red-900">{{ __('common.danger_zone') }}</h3>
				<p class="mt-1 text-sm text-red-700">
					{{ __('common.delete_all_categories_warning') }}
				</p>
			</div>
			<x-admin.button
				variant="danger"
				size="md"
				wire:click="deleteAllCategories"
				onclick="if(!confirm('{{ __('common.confirm_delete_all_categories') }}')){event.preventDefault();event.stopImmediatePropagation();}"
			>
				{{ __('common.delete_all_categories') }}
			</x-admin.button>
		</div>
	</div>

	<!-- Modal for create/edit -->
	@if ($showModal)
		<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="$set('showModal', false)">
			<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" wire:click.stop>
				<x-admin.card :title="$editingId ? __('common.editing_category') : __('common.creating_category')">
					<form wire:submit="save" class="space-y-4">
						<x-admin.input
							:label="__('common.name')"
							type="text"
							wire:model="name"
							required
							autofocus
							:error="$errors->first('name')"
						/>

						<x-admin.input
							:label="__('common.description')"
							type="textarea"
							wire:model="description"
							:error="$errors->first('description')"
						/>

						<div class="flex items-center gap-4">
							<x-admin.button variant="primary" type="submit">
								{{ __('common.save') }}
							</x-admin.button>
							<x-admin.button variant="secondary" wire:click="$set('showModal', false)">
								{{ __('common.cancel') }}
							</x-admin.button>
						</div>
					</form>
				</x-admin.card>
			</div>
		</div>
	@endif
</div>

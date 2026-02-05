<div class="space-y-6">
	<x-admin.page-header
		:title="$supplier ? __('common.editing_supplier') : __('common.creating_supplier')"
		:subtitle="$supplier ? __('common.editing_supplier_subtitle') : __('common.creating_supplier_subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.suppliers.index')">
				{{ __('common.back') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.card>
		<form wire:submit="save" class="space-y-6">
			@if ($isAdmin)
				<x-admin.select
					:label="__('common.company')"
					wire:model="company_id"
					:error="$errors->first('company_id')"
				>
					<option value="">{{ __('common.company_not_set') }}</option>
					@foreach ($companies as $company)
						<option value="{{ $company->id }}">{{ $company->company_name ?? $company->name }}</option>
					@endforeach
				</x-admin.select>
			@endif

			<x-admin.input
				:label="__('common.name')"
				type="text"
				wire:model="name"
				required
				autofocus
				:error="$errors->first('name')"
			/>

			<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
				<x-admin.input
					:label="__('common.contact_name')"
					type="text"
					wire:model="contact_name"
					:error="$errors->first('contact_name')"
				/>

				<x-admin.input
					:label="__('common.phone')"
					type="text"
					wire:model="phone"
					:error="$errors->first('phone')"
				/>

				<x-admin.input
					:label="__('common.email')"
					type="email"
					wire:model="email"
					:error="$errors->first('email')"
				/>

				<x-admin.input
					:label="__('common.website')"
					type="text"
					wire:model="website"
					:error="$errors->first('website')"
				/>
			</div>

			<x-admin.input
				:label="__('common.comment')"
				type="textarea"
				wire:model="comment"
				:error="$errors->first('comment')"
			/>

			<div class="space-y-2">
				<div class="text-sm font-semibold text-slate-700">{{ __('common.photo') }}</div>
				<input
					type="file"
					wire:model="photo"
					accept="image/*"
					class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
				/>
				@if ($errors->has('photo'))
					<div class="text-xs text-red-600">{{ $errors->first('photo') }}</div>
				@endif

				@if ($photo)
					<img src="{{ $photo->temporaryUrl() }}" alt="Photo preview" class="h-24 w-24 rounded-lg object-cover border border-slate-200" />
				@elseif ($supplier && $supplier->photo_path)
					<img src="{{ asset('storage/' . $supplier->photo_path) }}" alt="Photo" class="h-24 w-24 rounded-lg object-cover border border-slate-200" />
				@endif
			</div>

			<div class="flex items-center gap-4">
				<x-admin.button variant="primary" type="submit">
					{{ __('common.save') }}
				</x-admin.button>
				@if ($supplier)
					<x-admin.button variant="secondary" :href="route('admin.suppliers.show', $supplier)">
						{{ __('common.cancel') }}
					</x-admin.button>
				@endif
			</div>
		</form>
	</x-admin.card>
</div>

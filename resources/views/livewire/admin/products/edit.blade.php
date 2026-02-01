<div class="space-y-6">
	<x-admin.page-header
		:title="$product ? __('common.editing_product') : __('common.creating_product')"
		:subtitle="$product ? __('common.editing_product_subtitle') : __('common.creating_product_subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.products.index')">
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

			<x-admin.select
				:label="__('common.category')"
				wire:model="category_id"
				:error="$errors->first('category_id')"
			>
				<option value="">{{ __('common.category_not_set') }}</option>
				@foreach ($categories as $cat)
					<option value="{{ $cat->id }}">{{ $cat->name }}</option>
				@endforeach
			</x-admin.select>

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
				@if ($product)
					<x-admin.button variant="secondary" :href="route('admin.products.show', $product)">
						{{ __('common.cancel') }}
					</x-admin.button>
				@endif
			</div>
		</form>
	</x-admin.card>
</div>
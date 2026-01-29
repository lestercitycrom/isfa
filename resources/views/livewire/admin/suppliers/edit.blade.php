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

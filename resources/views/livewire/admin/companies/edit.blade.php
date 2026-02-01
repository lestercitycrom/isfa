<div class="space-y-6">
	<x-admin.page-header
		:title="$company ? __('common.editing_company') : __('common.creating_company')"
		:subtitle="$company ? __('common.editing_company_subtitle') : __('common.creating_company_subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.companies.index')">
				{{ __('common.back') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<x-admin.card>
		<form wire:submit="save" class="space-y-6">
			<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
				<x-admin.input
					:label="__('common.company_name')"
					type="text"
					wire:model="company_name"
					required
					autofocus
					:error="$errors->first('company_name')"
				/>

				<x-admin.input
					:label="__('common.legal_name')"
					type="text"
					wire:model="legal_name"
					:error="$errors->first('legal_name')"
				/>
			</div>

			<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
				<x-admin.input
					:label="__('common.tax_id')"
					type="text"
					wire:model="tax_id"
					:error="$errors->first('tax_id')"
				/>

				<x-admin.input
					:label="__('common.registration_number')"
					type="text"
					wire:model="registration_number"
					:error="$errors->first('registration_number')"
				/>

				<x-admin.input
					:label="__('common.website')"
					type="text"
					wire:model="website"
					:error="$errors->first('website')"
				/>
			</div>

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
			</div>

			<x-admin.input
				:label="__('common.address')"
				type="text"
				wire:model="address"
				:error="$errors->first('address')"
			/>

			<x-admin.input
				:label="__('common.notes')"
				type="textarea"
				wire:model="notes"
				:error="$errors->first('notes')"
			/>

			<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
				<x-admin.input
					:label="__('common.email')"
					type="email"
					wire:model="email"
					required
					:error="$errors->first('email')"
				/>

				<x-admin.input
					:label="__('common.password')"
					type="text"
					wire:model="password"
					:placeholder="$company ? '' : __('common.password')"
					:required="$company === null"
					:error="$errors->first('password')"
				/>
			</div>

			<div class="flex items-center gap-4">
				<x-admin.button variant="primary" type="submit">
					{{ __('common.save') }}
				</x-admin.button>
				@if ($company)
					<x-admin.button variant="secondary" :href="route('admin.companies.show', $company)">
						{{ __('common.cancel') }}
					</x-admin.button>
				@endif
			</div>
		</form>
	</x-admin.card>
</div>

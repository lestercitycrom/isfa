<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.update_password')"
		:subtitle="__('common.update_password_subtitle')"
	/>

	<x-admin.card>
		<form wire:submit="updatePassword" class="space-y-6">
			<x-admin.input
				:label="__('common.current_password')"
				type="password"
				wire:model="current_password"
				required
				autocomplete="current-password"
				:error="$errors->first('current_password')"
			/>

			<x-admin.input
				:label="__('common.new_password')"
				type="password"
				wire:model="password"
				required
				autocomplete="new-password"
				:error="$errors->first('password')"
			/>

			<x-admin.input
				:label="__('common.confirm_password')"
				type="password"
				wire:model="password_confirmation"
				required
				autocomplete="new-password"
				:error="$errors->first('password_confirmation')"
			/>

			<div class="flex items-center gap-4">
				<x-admin.button variant="primary" type="submit">
					{{ __('common.save') }}
				</x-admin.button>
			</div>
		</form>
	</x-admin.card>
</div>

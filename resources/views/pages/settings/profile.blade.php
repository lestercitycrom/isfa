<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.profile')"
		:subtitle="__('common.profile_subtitle')"
	/>

	<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
		<x-admin.card :title="__('common.account_data')">
			<form wire:submit="updateProfileInformation" class="space-y-6">
				<x-admin.input
					:label="__('common.name')"
					type="text"
					wire:model="name"
					required
					autofocus
					autocomplete="name"
					:error="$errors->first('name')"
				/>

				<div>
					<x-admin.input
						:label="__('common.email')"
						type="email"
						wire:model="email"
						required
						autocomplete="email"
						:error="$errors->first('email')"
					/>

					@if ($this->hasUnverifiedEmail)
						<div class="mt-4">
							<x-admin.alert variant="warning" :autohide="false" :dismissible="false">
								<div class="font-semibold">{{ __('common.email_unverified') }}</div>
								<div class="mt-1 text-sm">
									<button type="button" class="underline" wire:click.prevent="resendVerificationNotification">
										{{ __('common.resend_verification') }}
									</button>
								</div>
							</x-admin.alert>

							@if (session('status') === 'verification-link-sent')
								<x-admin.alert class="mt-3" variant="success" :autohide="false" :dismissible="false">
									{{ __('common.verification_link_sent') }}
								</x-admin.alert>
							@endif
						</div>
					@endif
				</div>

				<div class="flex items-center gap-4">
					<x-admin.button variant="primary" type="submit" data-test="update-profile-button">
						{{ __('common.save') }}
					</x-admin.button>

					<x-action-message class="text-sm text-slate-600" on="profile-updated">
						{{ __('common.saved') }}
					</x-action-message>
				</div>
			</form>
		</x-admin.card>

		<x-admin.card :title="__('common.change_password')">
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
					<x-admin.button variant="primary" type="submit" data-test="update-password-button">
						{{ __('common.save') }}
					</x-admin.button>

					<x-action-message class="text-sm text-slate-600" on="password-updated">
						{{ __('common.saved') }}
					</x-action-message>
				</div>
			</form>
		</x-admin.card>
	</div>
</div>

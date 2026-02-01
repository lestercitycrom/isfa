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

	@if ($isCompany)
		<x-admin.card :title="__('common.company_details')">
			<form wire:submit="updateCompanyInformation" class="space-y-6">
				<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
					<x-admin.input
						:label="__('common.company_name')"
						type="text"
						wire:model="company_name"
						required
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

				<div class="flex items-center gap-4">
					<x-admin.button variant="primary" type="submit">
						{{ __('common.save') }}
					</x-admin.button>

					<x-action-message class="text-sm text-slate-600" on="company-updated">
						{{ __('common.saved') }}
					</x-action-message>
				</div>
			</form>
		</x-admin.card>
	@endif
</div>

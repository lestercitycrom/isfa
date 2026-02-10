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

			@if ($company === null)
				<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
					<div class="text-sm font-semibold text-slate-700">{{ __('common.account_details') }}</div>
					<div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
						<x-admin.input
							:label="__('common.account_name')"
							type="text"
							wire:model="account_name"
							required
							:error="$errors->first('account_name')"
						/>

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
							required
							:error="$errors->first('password')"
						/>
					</div>
					<p class="mt-2 text-xs text-slate-500">{{ __('common.account_details_hint') }}</p>
				</div>
			@endif

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

	@if ($company)
		<x-admin.card :title="__('common.accounts')">
			<div class="space-y-4">
				<div class="grid grid-cols-1 gap-3">
					@forelse ($company->users as $account)
						<div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3">
							<div x-data="{ showPassword: false }">
								<div class="text-sm font-semibold text-slate-900">{{ $account->name }}</div>
								<div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
									<span>{{ $account->email }}</span>
									<span class="text-slate-300">/</span>
									<span class="font-medium text-slate-700" x-text="showPassword ? @js((string) ($account->password_plain ?? '')) : '••••••'"></span>
									<button
										type="button"
										class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100"
										x-on:click="showPassword = !showPassword"
										title="{{ __('common.view') }}"
									>
										<x-admin.icon name="eye" class="h-3.5 w-3.5" />
									</button>
								</div>
							</div>
							<div class="flex items-center gap-4">
								<label class="inline-flex items-center gap-2 text-sm text-slate-700">
									<input
										type="checkbox"
										class="rounded border-slate-300 text-slate-900 focus:ring-slate-300"
										wire:model.live="accountReminderFlags.{{ $account->id }}"
									/>
									<span>{{ __('common.receive_tender_reminders') }}</span>
								</label>

								<x-admin.icon-button
									icon="trash"
									:title="__('common.delete')"
									variant="danger"
									wire:click="deleteAccount({{ $account->id }})"
									onclick="if(!confirm('{{ __('common.confirm_delete') }}')){event.preventDefault();event.stopImmediatePropagation();}"
								/>
							</div>
						</div>
					@empty
						<div class="text-sm text-slate-500">{{ __('common.no_accounts') }}</div>
					@endforelse
				</div>

				<form wire:submit.prevent="addAccount" class="space-y-3">
					<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
						<x-admin.input
							:label="__('common.account_name')"
							type="text"
							wire:model="account_name"
							:error="$errors->first('account_name')"
							required
						/>
						<x-admin.input
							:label="__('common.email')"
							type="email"
							wire:model="account_email"
							:error="$errors->first('account_email')"
							required
						/>
						<x-admin.input
							:label="__('common.password')"
							type="text"
							wire:model="account_password"
							:error="$errors->first('account_password')"
							required
						/>
					</div>

					<div class="flex items-center gap-4">
						<x-admin.button variant="primary" type="submit">
							{{ __('common.add_account') }}
						</x-admin.button>
					</div>
				</form>
			</div>
		</x-admin.card>
	@endif
</div>

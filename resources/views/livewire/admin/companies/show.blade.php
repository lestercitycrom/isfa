<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.company_details')"
		:subtitle="$company->name"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.companies.index')">
				{{ __('common.back') }}
			</x-admin.button>
			<x-admin.button variant="primary" :href="route('admin.companies.edit', $company)">
				<x-admin.icon name="pencil" class="h-4 w-4" />
				{{ __('common.edit') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

	<div class="flex flex-wrap gap-2">
		<button
			type="button"
			wire:click="setTab('details')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'details' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.tab_details') }}
		</button>
		<button
			type="button"
			wire:click="setTab('comments')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'comments' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.comments') }}
		</button>
	</div>

	@if($tab === 'details')
		<x-admin.card>
			<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
				<div class="space-y-3">
					<div>
						<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.company_name') }}</div>
						<div class="text-base font-semibold text-slate-900">{{ $company->name }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.legal_name') }}</div>
						<div class="text-base text-slate-900">{{ $company->legal_name ?: '-' }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.tax_id') }}</div>
						<div class="text-base text-slate-900">{{ $company->tax_id ?: '-' }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.registration_number') }}</div>
						<div class="text-base text-slate-900">{{ $company->registration_number ?: '-' }}</div>
					</div>
				</div>

				<div class="space-y-3">
					<div>
						<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.contact_name') }}</div>
						<div class="text-base text-slate-900">{{ $company->contact_name ?: '-' }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.phone') }}</div>
						<div class="text-base text-slate-900">{{ $company->phone ?: '-' }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.website') }}</div>
						<div class="text-base text-slate-900">{{ $company->website ?: '-' }}</div>
					</div>
				</div>
			</div>

			<div class="mt-6">
				<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.address') }}</div>
				<div class="text-base text-slate-900">{{ $company->address ?: '-' }}</div>
			</div>
		</x-admin.card>

		<x-admin.card :title="__('common.accounts')">
			<div class="space-y-4">
				<div class="grid grid-cols-1 gap-3">
					@forelse ($accounts as $account)
						<div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3">
							<div>
								<div class="text-sm font-semibold text-slate-900">{{ $account->name }}</div>
								<div class="text-xs text-slate-500">{{ $account->email }}</div>
							</div>
							<x-admin.icon-button
								icon="trash"
								:title="__('common.delete')"
								variant="danger"
								wire:click="deleteAccount({{ $account->id }})"
								onclick="if(!confirm('{{ __('common.confirm_delete') }}')){event.preventDefault();event.stopImmediatePropagation();}"
							/>
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

	@if($tab === 'comments')
		<x-admin.card :title="__('common.comments')">
			<form wire:submit.prevent="saveComment" class="space-y-4">
				<x-admin.input
					:label="__('common.comment')"
					type="textarea"
					wire:model="comment"
				/>
				<div class="flex items-center gap-4">
					<x-admin.button variant="primary" type="submit">
						{{ __('common.save') }}
					</x-admin.button>
					<x-action-message class="text-sm text-slate-600" on="comment-saved">
						{{ __('common.saved') }}
					</x-action-message>
				</div>
			</form>
		</x-admin.card>
	@endif
</div>

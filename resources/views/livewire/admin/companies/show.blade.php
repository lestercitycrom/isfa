<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.company_details')"
		:subtitle="$company->company_name ?? $company->name"
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

	<x-admin.card>
		<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
			<div class="space-y-3">
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.company_name') }}</div>
					<div class="text-base font-semibold text-slate-900">{{ $company->company_name ?? $company->name }}</div>
				</div>
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.legal_name') }}</div>
					<div class="text-base text-slate-900">{{ $company->legal_name ?: '—' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.tax_id') }}</div>
					<div class="text-base text-slate-900">{{ $company->tax_id ?: '—' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.registration_number') }}</div>
					<div class="text-base text-slate-900">{{ $company->registration_number ?: '—' }}</div>
				</div>
			</div>

			<div class="space-y-3">
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.contact_name') }}</div>
					<div class="text-base text-slate-900">{{ $company->contact_name ?: '—' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.email') }}</div>
					<div class="text-base text-slate-900">{{ $company->email }}</div>
				</div>
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.phone') }}</div>
					<div class="text-base text-slate-900">{{ $company->phone ?: '—' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.website') }}</div>
					<div class="text-base text-slate-900">{{ $company->website ?: '—' }}</div>
				</div>
			</div>
		</div>

		<div class="mt-6">
			<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.address') }}</div>
			<div class="text-base text-slate-900">{{ $company->address ?: '—' }}</div>
		</div>

		<div class="mt-6">
			<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.notes') }}</div>
			<div class="text-base text-slate-900 whitespace-pre-line">{{ $company->notes ?: '—' }}</div>
		</div>

		<div class="mt-6">
			<div class="text-xs uppercase tracking-wide text-slate-500">{{ __('common.password') }}</div>
			<div class="text-base text-slate-900">{{ $company->password_plain ?: '—' }}</div>
		</div>
	</x-admin.card>
</div>

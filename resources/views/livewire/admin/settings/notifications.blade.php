<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.notifications_settings')"
		:subtitle="__('common.notifications_settings_subtitle')"
	/>

	<div class="flex flex-wrap gap-2">
		<button
			type="button"
			wire:click="setTab('email')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'email' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.email_templates') }}
		</button>
		<button
			type="button"
			wire:click="setTab('log')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'log' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.send_log') }}
		</button>
	</div>

	@if($tab === 'email')
		<x-admin.card :title="__('common.email_templates')">
			<form wire:submit.prevent="saveTemplates" class="space-y-6">
				<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
					<div class="text-sm font-semibold text-slate-900">{{ __('common.mail_connection') }}</div>
					<div class="mt-2 text-sm text-slate-700 space-y-1">
						<div>Mailer: <span class="font-medium">{{ $mailDefault }}</span></div>
						<div>SMTP: <span class="font-medium">{{ $mailHost }}:{{ $mailPort }}</span></div>
						<div>From: <span class="font-medium">{{ $mailFrom }}</span></div>
					</div>
				</div>

				<div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
					<div class="flex items-center justify-between gap-3">
						<div class="text-sm font-semibold text-slate-900">{{ __('common.reminder_7d') }}</div>
						<label class="inline-flex items-center gap-2 text-sm text-slate-700">
							<input type="checkbox" wire:model="active7d" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" />
							<span>{{ __('common.active') }}</span>
						</label>
					</div>
					<x-admin.input :label="__('common.subject')" type="text" wire:model="subject7d" :error="$errors->first('subject7d')" />
					<x-admin.input :label="__('common.body')" type="textarea" wire:model="body7d" :error="$errors->first('body7d')" />
				</div>

				<div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
					<div class="flex items-center justify-between gap-3">
						<div class="text-sm font-semibold text-slate-900">{{ __('common.reminder_3d') }}</div>
						<label class="inline-flex items-center gap-2 text-sm text-slate-700">
							<input type="checkbox" wire:model="active3d" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" />
							<span>{{ __('common.active') }}</span>
						</label>
					</div>
					<x-admin.input :label="__('common.subject')" type="text" wire:model="subject3d" :error="$errors->first('subject3d')" />
					<x-admin.input :label="__('common.body')" type="textarea" wire:model="body3d" :error="$errors->first('body3d')" />
				</div>

				<div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
					<div class="flex items-center justify-between gap-3">
						<div class="text-sm font-semibold text-slate-900">{{ __('common.reminder_1d') }}</div>
						<label class="inline-flex items-center gap-2 text-sm text-slate-700">
							<input type="checkbox" wire:model="active1d" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" />
							<span>{{ __('common.active') }}</span>
						</label>
					</div>
					<x-admin.input :label="__('common.subject')" type="text" wire:model="subject1d" :error="$errors->first('subject1d')" />
					<x-admin.input :label="__('common.body')" type="textarea" wire:model="body1d" :error="$errors->first('body1d')" />
				</div>

				<div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
					<div class="text-sm font-semibold text-slate-900">{{ __('common.available_variables') }}</div>
					<div class="mt-3 space-y-2">
						@foreach($availablePlaceholders as $placeholder)
							<div class="flex items-center gap-3 text-sm">
								<x-admin.badge variant="slate">{{ $placeholder }}</x-admin.badge>
								<span class="text-slate-600">{{ $placeholderDescriptions[$placeholder] ?? '-' }}</span>
							</div>
						@endforeach
					</div>
				</div>

				<div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
					<div class="text-sm font-semibold text-slate-900">{{ __('common.send_test_email') }}</div>
					<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
						<x-admin.input
							:label="__('common.email')"
							type="email"
							wire:model="testEmail"
							:error="$errors->first('testEmail')"
						/>
						<x-admin.select
							:label="__('common.template')"
							wire:model="testTemplateKey"
							:error="$errors->first('testTemplateKey')"
						>
							<option value="tender_reminder_7d">{{ __('common.reminder_7d') }}</option>
							<option value="tender_reminder_3d">{{ __('common.reminder_3d') }}</option>
							<option value="tender_reminder_1d">{{ __('common.reminder_1d') }}</option>
						</x-admin.select>
						<div class="flex items-end">
							<x-admin.button variant="secondary" type="button" wire:click="sendTestEmail" class="w-full">
								{{ __('common.send_test_email') }}
							</x-admin.button>
						</div>
					</div>
					<x-action-message class="text-sm text-slate-600" on="test-email-sent">
						{{ __('common.saved') }}
					</x-action-message>

					@if($testResultStatus !== null)
						<div class="rounded-xl border px-3 py-3 text-sm
							{{ $testResultStatus === 'success'
								? 'border-emerald-200 bg-emerald-50 text-emerald-800'
								: 'border-red-200 bg-red-50 text-red-800' }}">
							<div class="font-semibold">{{ $testResultMessage }}</div>
							@if($testResultDetails)
								<div class="mt-1 break-words">{{ $testResultDetails }}</div>
							@endif
						</div>
					@endif
				</div>

				<div class="flex items-center gap-4">
					<x-admin.button variant="primary" type="submit">
						{{ __('common.save') }}
					</x-admin.button>
					<x-action-message class="text-sm text-slate-600" on="templates-saved">
						{{ __('common.saved') }}
					</x-action-message>
				</div>
			</form>
		</x-admin.card>
	@endif

	@if($tab === 'log')
		<x-admin.card :title="__('common.send_log')">
			<x-admin.table :zebra="true">
				<x-slot name="head">
					<tr>
						<x-admin.th>{{ __('common.time') }}</x-admin.th>
						<x-admin.th>{{ __('common.user') }}</x-admin.th>
						<x-admin.th>{{ __('common.tender') }}</x-admin.th>
						<x-admin.th>{{ __('common.status') }}</x-admin.th>
						<x-admin.th>{{ __('common.details') }}</x-admin.th>
					</tr>
				</x-slot>

				@forelse($deliveries as $delivery)
					<tr>
						<x-admin.td>{{ $delivery->created_at?->format('Y-m-d H:i') ?? '-' }}</x-admin.td>
						<x-admin.td>
							<div class="font-medium text-slate-900">{{ $delivery->user?->name ?? '-' }}</div>
							<div class="text-xs text-slate-500">{{ $delivery->recipient_email }}</div>
						</x-admin.td>
						<x-admin.td>
							@if($delivery->tender)
								<a href="{{ route('admin.tenders.show', $delivery->tender) }}" class="underline hover:text-slate-700">
									{{ $delivery->tender->document_number ?: $delivery->tender->event_id }}
								</a>
								<div class="text-xs text-slate-500 mt-1">{{ $delivery->tender->title }}</div>
							@else
								-
							@endif
						</x-admin.td>
						<x-admin.td>
							<x-admin.badge variant="{{ $delivery->status === 'sent' ? 'green' : ($delivery->status === 'failed' ? 'red' : 'slate') }}">
								{{ $delivery->status }}
							</x-admin.badge>
						</x-admin.td>
						<x-admin.td>
							<div class="text-xs text-slate-600">
								<div>Type: {{ $delivery->reminder_type }}</div>
								@if($delivery->sent_at)
									<div>{{ __('common.sent_at') }}: {{ $delivery->sent_at->format('Y-m-d H:i') }}</div>
								@endif
								@if($delivery->error_message)
									<div class="mt-1 text-red-600">{{ $delivery->error_message }}</div>
								@endif
							</div>
						</x-admin.td>
					</tr>
				@empty
					<tr>
						<x-admin.td colspan="5" class="text-center py-8 text-slate-500">
							{{ __('common.no_records') }}
						</x-admin.td>
					</tr>
				@endforelse
			</x-admin.table>
		</x-admin.card>
	@endif
</div>

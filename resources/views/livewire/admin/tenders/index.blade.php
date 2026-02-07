<div class="space-y-6">
	<x-admin.page-header
		:title="__('tenders.index.title')"
		:subtitle="__('tenders.index.subtitle')"
	/>

	@if (session('status'))
		<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
			{{ session('status') }}
		</div>
	@endif

	<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
	<form wire:submit.prevent="syncFromUrl" class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_auto] md:items-end">
		<div>
			<label class="block text-sm font-semibold text-slate-900">
				{{ __('tenders.import.label') }}
			</label>

			<input
				type="text"
				wire:model.defer="importUrl"
				placeholder="{{ __('tenders.import.placeholder') }}"
				class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
			/>

				@error('importUrl')
					<div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
				@enderror

				<div class="mt-2 text-xs text-slate-500">
					{{ __('tenders.import.hint_redirect') }}
				</div>
			</div>

			<div class="flex flex-col items-start md:items-end">
				<x-admin.button
					variant="primary"
					type="submit"
					class="min-w-[10.5rem] justify-center"
					wire:loading.attr="disabled"
					wire:target="syncFromUrl"
				>
					<span wire:loading.remove wire:target="syncFromUrl">
						{{ __('tenders.actions.add') }}
					</span>
					<span wire:loading wire:target="syncFromUrl">
						{{ __('tenders.actions.parsing') }}
					</span>
				</x-admin.button>

				<div class="mt-2 h-5 text-sm text-slate-500" aria-live="polite">
					<span wire:loading wire:target="syncFromUrl">
						{{ __('tenders.import.do_not_close_tab') }}
					</span>
				</div>
			</div>
		</form>
	</div>

	<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
		<div class="border-b border-slate-200 p-4">
			<div class="grid grid-cols-1 gap-3 md:grid-cols-3">
				<div>
					<label class="block text-xs font-semibold text-slate-600">{{ __('tenders.filters.search') }}</label>
					<input
						type="text"
						wire:model.live="search"
						placeholder="{{ __('tenders.filters.search_placeholder') }}"
						class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
					/>
				</div>

				@if ($isAdmin)
					<div>
						<label class="block text-xs font-semibold text-slate-600">{{ __('common.company') }}</label>
						<select
							wire:model.live="companyFilter"
							class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
						>
							<option value="">{{ __('common.all_companies') }}</option>
							<option value="0">{{ __('common.admin') }}</option>
							@foreach ($companies as $company)
								<option value="{{ $company->id }}">{{ $company->company_name ?? $company->name }}</option>
							@endforeach
						</select>
					</div>
				@endif

				<div>
					<label class="block text-xs font-semibold text-slate-600">{{ __('common.tags') }}</label>
					<select
						wire:model.live="tagFilter"
						class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
					>
						<option value="">{{ __('common.tags') }}</option>
						@foreach ($tagOptions as $tag)
							<option value="{{ $tag->id }}">{{ $tag->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
		</div>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr class="text-left text-xs font-semibold text-slate-600">
						<th class="px-4 py-3">{{ __('common.event_id') }}</th>
						<th class="px-4 py-3">{{ __('tenders.table.title') }}</th>
						<th class="px-4 py-3">{{ __('common.organization') }}</th>
						<th class="px-4 py-3">{{ __('tenders.table.published') }}</th>
						<th class="px-4 py-3">{{ __('common.tags') }}</th>
						@if ($isAdmin)
							<th class="px-4 py-3">{{ __('common.company') }}</th>
						@endif
						<th class="px-4 py-3 text-right">{{ __('tenders.table.actions') }}</th>
					</tr>
				</thead>

				<tbody class="divide-y divide-slate-200 bg-white">
					@forelse ($tenders as $tender)
						<tr class="text-sm text-slate-900">
							<td class="px-4 py-3 font-mono text-xs text-slate-700">
								{{ $tender->event_id }}
							</td>

							<td class="px-4 py-3">
								<div class="font-semibold">{{ $tender->title }}</div>
								<div class="mt-1 text-xs text-slate-500">
									{{ __('common.document_number') }}: {{ $tender->document_number ?: '—' }}
								</div>
							</td>

							<td class="px-4 py-3">
								<div class="text-sm">{{ $tender->organization_name ?: '—' }}</div>
								<div class="mt-1 text-xs text-slate-500">
									{{ __('common.voen') }}: {{ $tender->organization_voen ?: '—' }}
								</div>
							</td>

							<td class="px-4 py-3 text-sm text-slate-700">
								{{ $tender->published_at?->format('Y-m-d H:i') ?: '—' }}
							</td>

							<td class="px-4 py-3">
								@if($tender->tags->isNotEmpty())
									<div class="flex flex-wrap gap-1">
										@foreach ($tender->tags->take(3) as $tag)
											<x-admin.badge variant="slate">{{ $tag->name }}</x-admin.badge>
										@endforeach
										@if ($tender->tags->count() > 3)
											<x-admin.badge variant="slate">+{{ $tender->tags->count() - 3 }}</x-admin.badge>
										@endif
									</div>
								@else
									<span class="text-slate-400">-</span>
								@endif
							</td>

							@if ($isAdmin)
								<td class="px-4 py-3 text-sm text-slate-700">
									{{ $tender->company?->company_name ?? $tender->company?->name ?? '—' }}
								</td>
							@endif

							<td class="px-4 py-3 text-right">
								<div class="inline-flex items-center gap-2">
									<a
										class="inline-flex items-center justify-center rounded-xl h-9 w-9 transition focus:outline-none focus:ring-2 focus:ring-offset-2 bg-white text-slate-900 border border-slate-200 hover:bg-slate-50 focus:ring-slate-300"
										href="{{ route('admin.tenders.show', $tender) }}"
										title="{{ __('tenders.actions.open') }}"
										aria-label="{{ __('tenders.actions.open') }}"
									>
										<x-admin.icon name="eye" class="h-4 w-4" />
									</a>
									<x-admin.icon-button
										wire:click="delete({{ $tender->id }})"
										onclick="return confirm('{{ __('common.confirm_delete') }}')"
										icon="trash"
										:title="__('common.delete')"
										variant="danger"
									/>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="{{ $isAdmin ? 7 : 6 }}" class="px-4 py-8 text-center text-sm text-slate-500">
								{{ __('tenders.table.empty') }}
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if ($tenders->hasPages())
			<div class="border-t border-slate-200 p-4">
				{{ $tenders->links() }}
			</div>
		@endif
	</div>
</div>

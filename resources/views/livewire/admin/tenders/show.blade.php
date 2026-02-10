<div class="space-y-6">
	<x-admin.page-header
		:title="$tender->title"
		:subtitle="$tender->organization_name ?: __('common.tender')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.tenders.index')">
				{{ __('common.back') }}
			</x-admin.button>
			<a
				class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ route('admin.export.tenders.items.excel', $tender) }}"
			>
				<x-admin.icon name="download" class="h-4 w-4" />
				{{ __('common.export_excel') }}
			</a>

			<a
				class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
				href="{{ $originalUrl }}"
				target="_blank"
				rel="noopener noreferrer"
			>
				<x-admin.icon name="external-link" class="h-4 w-4" />
				{{ __('common.open_original') }}
			</a>
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
			wire:click="setTab('item-suppliers')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'item-suppliers' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.items') }} / {{ __('common.suppliers') }}
		</button>
		<button
			type="button"
			wire:click="setTab('history')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'history' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			{{ __('common.tab_history') }}
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

	<x-admin.card :title="__('common.tags')">
		<x-admin.tag-manager
			:selected-tags="$tender->tags"
			:tag-suggestions="$tagSuggestions"
			:show-tag-dropdown="$showTagDropdown"
		/>
	</x-admin.card>

	@if($tab === 'details')
		<x-admin.card :title="__('common.summary')">
			<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
				<div class="rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-xs text-slate-500">{{ __('common.event_id') }}</div>
					<div class="mt-1 text-lg font-semibold text-slate-900">{{ $tender->event_id }}</div>

					@if($tender->document_number)
						<div class="mt-2 text-xs text-slate-500">{{ __('common.document_number') }}</div>
						<div class="mt-1 text-sm font-medium text-slate-800">{{ $tender->document_number }}</div>
					@endif
				</div>

				<div class="rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-xs text-slate-500">{{ __('common.organization') }}</div>
					<div class="mt-1 text-sm font-semibold text-slate-900">
						{{ $tender->organization_name ?: '-' }}
					</div>

					@if($tender->organization_voen)
						<div class="mt-2 text-xs text-slate-500">{{ __('common.voen') }}</div>
						<div class="mt-1 text-sm font-medium text-slate-800">{{ $tender->organization_voen }}</div>
					@endif
				</div>

				<div class="rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-xs text-slate-500">{{ __('common.amount') }}</div>
					<div class="mt-1 text-lg font-semibold text-slate-900">
						@if($tender->estimated_amount !== null)
							{{ number_format((float) $tender->estimated_amount, 2, '.', ' ') }}
						@else
							-
						@endif
					</div>

					<div class="mt-3 flex flex-wrap gap-2">
						@if($tender->document_view_type_code)
							@php
								$viewLabel = $dictLabels['document_view_type'][$tender->document_view_type_code] ?? $tender->document_view_type_code;
							@endphp
							<x-admin.badge variant="green">{{ __('common.document_view_type') }}: {{ $viewLabel }}</x-admin.badge>
						@endif
					</div>
				</div>
			</div>

			<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
				<div class="rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-sm font-semibold text-slate-900">{{ __('common.dates') }}</div>
					<div class="mt-3 space-y-2 text-sm text-slate-700">
						<div class="flex items-center justify-between gap-4">
							<span class="text-slate-500">{{ __('common.published_at') }}</span>
							<span class="font-medium">{{ $tender->published_at?->format('Y-m-d H:i') ?? '-' }}</span>
						</div>
						<div class="flex items-center justify-between gap-4">
							<span class="text-slate-500">{{ __('common.start_at') }}</span>
							<span class="font-medium">{{ $tender->start_at?->format('Y-m-d H:i') ?? '-' }}</span>
						</div>
						<div class="flex items-center justify-between gap-4">
							<span class="text-slate-500">{{ __('common.end_at') }}</span>
							<span class="font-medium">{{ $tender->end_at?->format('Y-m-d H:i') ?? '-' }}</span>
						</div>
						<div class="flex items-center justify-between gap-4">
							<span class="text-slate-500">{{ __('common.envelope_at') }}</span>
							<span class="font-medium">{{ $tender->envelope_at?->format('Y-m-d H:i') ?? '-' }}</span>
						</div>
					</div>
				</div>

				<div class="rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-sm font-semibold text-slate-900">{{ __('common.fees') }}</div>
					<div class="mt-3 space-y-2 text-sm text-slate-700">
						<div class="flex items-center justify-between gap-4">
							<span class="text-slate-500">{{ __('common.view_fee') }}</span>
							<span class="font-medium">
								@if($tender->view_fee !== null)
									{{ number_format((float) $tender->view_fee, 2, '.', ' ') }}
								@else
									-
								@endif
							</span>
						</div>
						<div class="flex items-center justify-between gap-4">
							<span class="text-slate-500">{{ __('common.participation_fee') }}</span>
							<span class="font-medium">
								@if($tender->participation_fee !== null)
									{{ number_format((float) $tender->participation_fee, 2, '.', ' ') }}
								@else
									-
								@endif
							</span>
						</div>
						<div class="flex items-center justify-between gap-4">
							<span class="text-slate-500">{{ __('common.min_suppliers') }}</span>
							<span class="font-medium">{{ $tender->min_number_of_suppliers ?? '-' }}</span>
						</div>
					</div>
				</div>
			</div>

			@if($tender->address)
				<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-sm font-semibold text-slate-900">{{ __('common.address') }}</div>
					<div class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $tender->address }}</div>
				</div>
			@endif
		</x-admin.card>

		<details class="rounded-2xl border border-slate-200 bg-white shadow-sm" open>
	<summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-900">
		{{ __('common.items') }}
	</summary>
	<div class="p-4 pt-0">
		<x-admin.table :zebra="true">
			<x-slot name="head">
				<tr>
					<x-admin.th nowrap>#</x-admin.th>
					<x-admin.th nowrap>{{ __('common.photo') }}</x-admin.th>
					<x-admin.th>{{ __('common.name') }}</x-admin.th>
					<x-admin.th nowrap>{{ __('common.quantity_unit_compact') }}</x-admin.th>
					<x-admin.th>{{ __('common.suppliers') }}</x-admin.th>
				</tr>
			</x-slot>

			@forelse ($tender->items as $item)
				<tr class="hover:bg-slate-50/70">
					<x-admin.td nowrap class="text-slate-600">{{ $item->external_id }}</x-admin.td>
					<x-admin.td nowrap>
						<div class="h-12 w-12 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center">
							@if ($item->photo_path)
								<img src="{{ asset('storage/' . $item->photo_path) }}" alt="{{ __('common.photo') }}" class="h-full w-full object-cover" />
							@else
								<x-admin.icon name="image" class="h-4 w-4 text-slate-400" />
							@endif
						</div>
					</x-admin.td>
					<x-admin.td>
						<div class="font-medium text-slate-900">{{ $item->name ?: '-' }}</div>
						@if($item->description)
							<div class="mt-1 text-xs text-slate-500 whitespace-pre-line break-words">{{ $item->description }}</div>
						@endif
					</x-admin.td>
					<x-admin.td nowrap class="text-slate-700">
						@if($item->quantity !== null)
							<div class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1">
								<span class="font-semibold text-slate-900">{{ rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ' '), '0'), '.') }}</span>
								<span class="text-slate-500">{{ $item->unit_of_measure ?: '' }}</span>
							</div>
						@else
							-
						@endif
					</x-admin.td>
					<x-admin.td>
						@if ($item->suppliers->isNotEmpty())
							<div class="flex flex-wrap gap-1">
								@foreach ($item->suppliers->take(3) as $supplier)
									<a href="{{ route('admin.suppliers.show', $supplier) }}" class="inline-flex">
										<x-admin.badge variant="slate">{{ $supplier->name }}</x-admin.badge>
									</a>
								@endforeach
								@if ($item->suppliers->count() > 3)
									<x-admin.badge variant="slate">+{{ $item->suppliers->count() - 3 }}</x-admin.badge>
								@endif
							</div>
						@else
							<span class="text-slate-400">-</span>
						@endif
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
	</div>
</details>

		@if(false)
		<x-admin.card :title="__('common.contacts')">
			<x-admin.table :zebra="true">
				<x-slot name="head">
					<tr>
						<x-admin.th>{{ __('common.full_name') }}</x-admin.th>
						<x-admin.th>{{ __('common.position') }}</x-admin.th>
						<x-admin.th>{{ __('common.contact') }}</x-admin.th>
						<x-admin.th nowrap>{{ __('common.phone') }}</x-admin.th>
					</tr>
				</x-slot>

				@forelse ($tender->contacts as $c)
					<tr class="hover:bg-slate-50/70">
						<x-admin.td class="font-medium text-slate-900">{{ $c->full_name ?: '-' }}</x-admin.td>
						<x-admin.td class="text-slate-700">{{ $c->position ?: '-' }}</x-admin.td>
						<x-admin.td class="text-slate-700">
						@if($c->contact)
								{{ $c->contact }}
							@else
								-
							@endif
						</x-admin.td>
						<x-admin.td nowrap class="text-slate-700">{{ $c->phone_number ?: '-' }}</x-admin.td>
					</tr>
				@empty
					<tr>
						<x-admin.td colspan="4" class="text-center py-8 text-slate-500">
							{{ __('common.no_records') }}
						</x-admin.td>
					</tr>
				@endforelse
			</x-admin.table>
		</x-admin.card>

		<details class="rounded-2xl border border-slate-200 bg-white shadow-sm">
	<summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-900">
		{{ __('common.announcements') }}
	</summary>
	<div class="p-4 pt-0">
		<div class="space-y-3">
			@forelse ($tender->announcements as $a)
				<div class="rounded-2xl border border-slate-200 bg-white p-4">
					<div class="text-xs text-slate-500">
						{{ __('common.announcement_id') }}: {{ $a->external_id ?? '-' }}
						@if($a->announcement_version !== null)
							<span class="ml-2">{{ __('common.version') }}: {{ $a->announcement_version }}</span>
						@endif
					</div>
						<div class="mt-2 text-sm text-slate-800 whitespace-pre-wrap break-words overflow-hidden">
							{{ $a->text ? mb_convert_encoding((string) $a->text, 'UTF-8', 'UTF-8, Windows-1251') : '-' }}
						</div>
				</div>
			@empty
				<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
			@endforelse
		</div>
	</div>
</details>

		<x-admin.card :title="__('common.publish_history')">
			<div class="space-y-2">
				@forelse ($tender->publishHistories as $h)
					<div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-3">
						<div class="text-sm font-medium text-slate-900">{{ $h->published_at?->format('Y-m-d H:i') ?? '-' }}</div>
						<div class="text-xs text-slate-500">{{ __('common.utc') }}</div>
					</div>
				@empty
					<div class="text-sm text-slate-500">{{ __('common.no_records') }}</div>
				@endforelse
			</div>
		</x-admin.card>
		@endif
	@endif

	@if($tab === 'item-suppliers')
		<x-admin.card :title="__('common.tender_item')">
			<div class="space-y-6">
				<div>
					<x-admin.select :label="__('common.items')" wire:model.live="selectedItemId">
						<option value="0">-</option>
						@foreach ($tender->items as $item)
							<option value="{{ $item->id }}">#{{ $item->external_id }} - {{ $item->name ?: '—' }}</option>
						@endforeach
					</x-admin.select>
				</div>

				@if($selectedItem)
					<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
						<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
							<div class="mb-3 text-sm font-semibold text-slate-900">{{ __('common.photo') }}</div>
							<div class="space-y-3">
								<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
									@if ($itemPhoto)
										<img src="{{ $itemPhoto->temporaryUrl() }}" alt="{{ __('common.photo') }}" class="h-56 w-full object-cover" />
									@elseif ($selectedItem->photo_path)
										<img src="{{ asset('storage/' . $selectedItem->photo_path) }}" alt="{{ __('common.photo') }}" class="h-56 w-full object-cover" />
									@else
										<div class="flex h-56 items-center justify-center bg-slate-100 text-sm font-medium text-slate-500">
											{{ __('common.photo_not_set') }}
										</div>
									@endif
								</div>

								<label for="tender-item-photo" class="block cursor-pointer rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-center text-sm text-slate-600 transition hover:border-slate-400 hover:bg-slate-50">
									<span class="inline-flex items-center justify-center gap-2 font-semibold text-slate-800">
										<x-admin.icon name="upload" class="h-4 w-4" />
										{{ __('common.photo_upload_action') }}
									</span>
									<span class="block mt-1 text-xs">{{ __('common.photo_upload_hint') }}</span>
								</label>
								<input id="tender-item-photo" type="file" wire:model.live="itemPhoto" accept="image/*" class="sr-only" />

								<div wire:loading wire:target="itemPhoto" class="text-xs font-medium text-slate-500">
									{{ __('common.photo_uploading') }}
								</div>

								@if ($errors->has('itemPhoto'))
									<div class="text-xs text-red-600">{{ $errors->first('itemPhoto') }}</div>
								@endif

								<div class="flex items-center gap-2">
									<x-admin.button variant="primary" wire:click="saveItemPhoto">
										{{ __('common.save') }}
									</x-admin.button>

									@if ($itemPhoto || $selectedItem->photo_path)
										<button
											type="button"
											wire:click="removeItemPhoto"
											class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
										>
											<x-admin.icon name="trash" class="h-4 w-4" />
											{{ __('common.photo_remove') }}
										</button>
									@endif
								</div>
							</div>
						</div>

						<div class="space-y-4">
							<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
								<div class="grid grid-cols-1 gap-3">
									<div class="relative">
										<label class="block text-sm font-medium text-slate-700">{{ __('common.search_supplier') }}</label>
										<input
											type="text"
											wire:model.live.debounce.300ms="supplierSearch"
											wire:focus="$set('showSupplierDropdown', true)"
											placeholder="{{ __('common.search_supplier') }}"
											class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
										>

										@if($showSupplierDropdown)
											<div class="absolute z-20 mt-1 max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-lg">
												@forelse($supplierOptions as $option)
													<button
														type="button"
														wire:click="selectSupplier({{ $option->id }})"
														class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-slate-50"
													>
														<span class="font-medium text-slate-900">{{ $option->name }}</span>
														<span class="text-xs text-slate-500">#{{ $option->id }}</span>
													</button>
												@empty
													<div class="px-3 py-2 text-sm text-slate-500">{{ __('common.no_records') }}</div>
												@endforelse
											</div>
										@endif
									</div>

									<div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
										<x-admin.select
											:label="__('common.status')"
											wire:model="attachStatus"
											:error="$errors->first('attachStatus')"
										>
											<option value="primary">{{ __('common.status_primary') }}</option>
											<option value="reserve">{{ __('common.status_reserve') }}</option>
										</x-admin.select>

										<div class="flex items-end">
											<x-admin.button variant="primary" wire:click="attachItemSupplier" class="w-full">
												{{ __('common.link') }}
											</x-admin.button>
										</div>
									</div>

									<x-admin.input
										:label="__('common.price_terms')"
										type="textarea"
										wire:model="attachTerms"
										:error="$errors->first('attachTerms')"
									/>
								</div>
							</div>
						</div>
					</div>

					<x-admin.table :zebra="true">
						<x-slot name="head">
							<tr>
								<x-admin.th>{{ __('common.supplier') }}</x-admin.th>
								<x-admin.th>{{ __('common.status') }}</x-admin.th>
								<x-admin.th>{{ __('common.price_terms') }}</x-admin.th>
								<x-admin.th align="right" nowrap>{{ __('common.actions') }}</x-admin.th>
							</tr>
						</x-slot>

						@forelse ($selectedItem->suppliers as $supplier)
							<tr class="hover:bg-slate-50/70">
								<x-admin.td>
									<a class="font-medium text-slate-900 underline hover:text-slate-700" href="{{ route('admin.suppliers.show', $supplier) }}">
										{{ $supplier->name }}
									</a>
								</x-admin.td>

								<x-admin.td>
									<x-admin.select wire:model="itemPivotStatus.{{ $supplier->id }}" size="sm">
										<option value="primary">{{ __('common.status_primary') }}</option>
										<option value="reserve">{{ __('common.status_reserve') }}</option>
									</x-admin.select>
								</x-admin.td>

								<x-admin.td>
									<x-admin.input
										type="textarea"
										wire:model="itemPivotTerms.{{ $supplier->id }}"
										size="sm"
									/>
								</x-admin.td>

								<x-admin.td align="right" nowrap>
									<div class="inline-flex items-center gap-2">
										<x-admin.button variant="secondary" size="sm" wire:click="saveItemSupplierPivot({{ $supplier->id }})">
											{{ __('common.save') }}
										</x-admin.button>
										<x-admin.icon-button
											icon="trash"
											:title="__('common.detach')"
											variant="danger"
											wire:click="detachItemSupplier({{ $supplier->id }})"
											onclick="if(!confirm('{{ __('common.confirm_detach') }}')){event.preventDefault();event.stopImmediatePropagation();}"
										/>
									</div>
								</x-admin.td>
							</tr>
						@empty
							<tr>
								<x-admin.td colspan="4" class="text-center py-8 text-slate-500">
									{{ __('common.no_linked_suppliers') }}
								</x-admin.td>
							</tr>
						@endforelse
					</x-admin.table>
				@else
					<div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-6 text-sm text-slate-600">
						{{ __('common.no_records') }}
					</div>
				@endif
			</div>
		</x-admin.card>
	@endif

	@if($tab === 'history')
		<x-admin.card :title="__('common.activity_history')">
			@include('partials.admin.activity-history', ['activities' => $activities])
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

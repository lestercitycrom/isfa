<div class="space-y-6">
	<x-admin.page-header
		title="Тендеры"
		subtitle="Список тендеров. Можно добавить тендер, вставив ссылку или eventId — парсинг выполнится автоматически."
	/>

	@if (session('status'))
		<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
			{{ session('status') }}
		</div>
	@endif

	<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
		<form wire:submit.prevent="syncFromUrl" class="flex flex-col gap-3 md:flex-row md:items-end">
			<div class="flex-1">
				<label class="block text-sm font-semibold text-slate-900">
					Ссылка на тендер или eventId
				</label>

				<input
					type="text"
					wire:model.defer="importUrl"
					placeholder="https://etender.gov.az/main/competition/detail/346012 или 346012"
					class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
				/>

				@error('importUrl')
					<div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
				@enderror

				<div class="mt-2 text-xs text-slate-500">
					После синхронизации ты будешь автоматически перенаправлен на детальную страницу тендера.
				</div>
			</div>

			<div class="flex items-center gap-2">
				<x-admin.button variant="primary" type="submit">
					<span wire:loading.remove wire:target="syncFromUrl">Добавить</span>
					<span wire:loading wire:target="syncFromUrl">Парсинг...</span>
				</x-admin.button>

				<div wire:loading wire:target="syncFromUrl" class="text-sm text-slate-500">
					Не закрывай вкладку
				</div>
			</div>
		</form>
	</div>

	<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
		<div class="border-b border-slate-200 p-4">
			<div class="grid grid-cols-1 gap-3 md:grid-cols-3">
				<div>
					<label class="block text-xs font-semibold text-slate-600">Поиск</label>
					<input
						type="text"
						wire:model.live="search"
						placeholder="Название, организация, eventId, номер документа..."
						class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
					/>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-600">Тип</label>
					<select
						wire:model.live="eventTypeFilter"
						class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
					>
						<option value="">Все</option>
						@foreach ($eventTypes as $type)
							<option value="{{ $type->code }}">
								{{ $type->label ?: $type->code }}
							</option>
						@endforeach
					</select>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-600">Статус</label>
					<select
						wire:model.live="eventStatusFilter"
						class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
					>
						<option value="">Все</option>
						@foreach ($eventStatuses as $status)
							<option value="{{ $status->code }}">
								{{ $status->label ?: $status->code }}
							</option>
						@endforeach
					</select>
				</div>
			</div>
		</div>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr class="text-left text-xs font-semibold text-slate-600">
						<th class="px-4 py-3">eventId</th>
						<th class="px-4 py-3">Название</th>
						<th class="px-4 py-3">Организация</th>
						<th class="px-4 py-3">Опубликован</th>
						<th class="px-4 py-3 text-right">Действия</th>
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
									Документ: {{ $tender->document_number ?: '—' }}
								</div>
							</td>

							<td class="px-4 py-3">
								<div class="text-sm">{{ $tender->organization_name ?: '—' }}</div>
								<div class="mt-1 text-xs text-slate-500">
									VOEN: {{ $tender->organization_voen ?: '—' }}
								</div>
							</td>

							<td class="px-4 py-3 text-sm text-slate-700">
								{{ $tender->published_at?->format('Y-m-d H:i') ?: '—' }}
							</td>

							<td class="px-4 py-3 text-right">
								<a
									class="inline-flex items-center justify-center rounded-xl h-9 w-9 transition focus:outline-none focus:ring-2 focus:ring-offset-2 bg-white text-slate-900 border border-slate-200 hover:bg-slate-50 focus:ring-slate-300"
									href="{{ route('admin.tenders.show', $tender) }}"
									title="Открыть"
									aria-label="Открыть"
								>
									<x-admin.icon name="eye" class="h-4 w-4" />
								</a>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
								Пока нет тендеров. Вставь ссылку сверху и нажми “Добавить”.
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

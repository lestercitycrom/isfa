<div class="space-y-6">
	<x-admin.page-header
		:title="$product ? __('common.editing_product') : __('common.creating_product')"
		:subtitle="$product ? __('common.editing_product_subtitle') : __('common.creating_product_subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.products.index')">
				{{ __('common.back') }}
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
			wire:click="setTab('attributes')"
			class="px-4 py-2 text-sm font-semibold rounded-xl border transition
				{{ $tab === 'attributes' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
		>
			Кастомные характеристики
		</button>
		@if ($product)
			<button
				type="button"
				wire:click="setTab('suppliers')"
				class="px-4 py-2 text-sm font-semibold rounded-xl border transition
					{{ $tab === 'suppliers' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
			>
				{{ __('common.suppliers_for_product') }}
			</button>
		@endif
	</div>

	@if ($tab === 'details')
		<x-admin.card>
			<form wire:submit="save" class="space-y-8">
				<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
					<div class="xl:col-span-1">
						<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
							<div class="mb-3 text-sm font-semibold text-slate-900">{{ __('common.photo') }}</div>
							<div class="space-y-3">
								<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
									@if ($photo)
										<img src="{{ $photo->temporaryUrl() }}" alt="{{ __('common.photo') }}" class="h-64 w-full object-cover" />
									@elseif ($product && $product->photo_path)
										<img src="{{ asset('storage/' . $product->photo_path) }}" alt="{{ __('common.photo') }}" class="h-64 w-full object-cover" />
									@else
										<div class="flex h-64 items-center justify-center bg-slate-100 text-sm font-medium text-slate-500">
											{{ __('common.photo_not_set') }}
										</div>
									@endif
								</div>

								<label for="product-photo" class="block cursor-pointer rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-center text-sm text-slate-600 transition hover:border-slate-400 hover:bg-slate-50">
									<span class="inline-flex items-center justify-center gap-2 font-semibold text-slate-800">
										<x-admin.icon name="upload" class="h-4 w-4" />
										{{ __('common.photo_upload_action') }}
									</span>
									<span class="block mt-1 text-xs">{{ __('common.photo_upload_hint') }}</span>
								</label>
								<input id="product-photo" type="file" wire:model.live="photo" accept="image/*" class="sr-only" />

								<div wire:loading wire:target="photo" class="text-xs font-medium text-slate-500">
									{{ __('common.photo_uploading') }}
								</div>

								@if ($errors->has('photo'))
									<div class="text-xs text-red-600">{{ $errors->first('photo') }}</div>
								@endif

								@if ($photo || ($product && $product->photo_path))
									<button
										type="button"
										wire:click="removePhoto"
										class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
									>
										<x-admin.icon name="trash" class="h-4 w-4" />
										{{ __('common.photo_remove') }}
									</button>
								@endif
							</div>
						</div>
					</div>

					<div class="xl:col-span-2 space-y-6">
						@if ($isAdmin)
							<x-admin.select
								:label="__('common.company')"
								wire:model="company_id"
								:error="$errors->first('company_id')"
							>
								<option value="">{{ __('common.company_not_set') }}</option>
								@foreach ($companies as $company)
									<option value="{{ $company->id }}">{{ $company->company_name ?? $company->name }}</option>
								@endforeach
							</x-admin.select>
						@endif

						<x-admin.select
							:label="__('common.category')"
							wire:model="category_id"
							:error="$errors->first('category_id')"
						>
							<option value="">{{ __('common.category_not_set') }}</option>
							@foreach ($categories as $cat)
								<option value="{{ $cat->id }}">{{ $cat->name }}</option>
							@endforeach
						</x-admin.select>

						<x-admin.input
							:label="__('common.name')"
							type="text"
							wire:model="name"
							required
							autofocus
							:error="$errors->first('name')"
						/>

						<x-admin.input
							:label="__('common.description')"
							type="textarea"
							wire:model="description"
							:error="$errors->first('description')"
						/>

						<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
							<div class="text-sm font-semibold text-slate-900">{{ __('common.product_details') }}</div>
							<div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
								<x-admin.input
									:label="__('common.color')"
									type="text"
									wire:model="color"
									:error="$errors->first('color')"
								/>
								<x-admin.input
									:label="__('common.unit')"
									type="text"
									wire:model="unit"
									:error="$errors->first('unit')"
								/>
							</div>
							<div class="mt-4">
								<x-admin.input
									:label="__('common.characteristics')"
									type="textarea"
									wire:model="characteristics"
									:error="$errors->first('characteristics')"
								/>
							</div>
						</div>
					</div>
				</div>

				<div class="flex items-center gap-4">
					<x-admin.button variant="primary" type="submit">
						{{ __('common.save') }}
					</x-admin.button>
					@if ($product)
						<x-admin.button variant="secondary" :href="route('admin.products.show', $product)">
							{{ __('common.cancel') }}
						</x-admin.button>
					@endif
				</div>
			</form>
		</x-admin.card>
	@endif

	@if ($tab === 'attributes')
		<x-admin.card>
			<div class="space-y-6">
				<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
					<div class="text-sm font-semibold text-slate-900">Справочник характеристик</div>
					<div class="mt-4 grid grid-cols-1 gap-3 xl:grid-cols-4">
						<div class="xl:col-span-2">
							<x-admin.input
								label="Название характеристики"
								type="text"
								wire:model="newAttributeLabel"
								:error="$errors->first('newAttributeLabel')"
							/>
						</div>
						<div>
							<x-admin.input
								label="Код (опционально)"
								type="text"
								wire:model="newAttributeCode"
								:error="$errors->first('newAttributeCode')"
							/>
						</div>
						<div>
							<x-admin.select
								label="Тип поля"
								wire:model="newAttributeType"
								:error="$errors->first('newAttributeType')"
							>
								@if(app()->getLocale() === 'az')
									<option value="text">M&#601;tn</option>
								@else
									<option value="text">&#1058;&#1077;&#1082;&#1089;&#1090;</option>
								@endif
							</x-admin.select>
						</div>
					</div>

					<div class="mt-4 flex items-center gap-3">
						<x-admin.button variant="secondary" wire:click="createAttributeDefinition">
							Добавить характеристику
						</x-admin.button>
						@if ($errors->has('newAttributeCode'))
							<div class="text-xs text-red-600">{{ $errors->first('newAttributeCode') }}</div>
						@endif
					</div>

					@if ($attributeDefinitions->isNotEmpty())
						<div class="mt-5 space-y-2">
							@foreach ($attributeDefinitions as $definition)
								<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
									<div class="min-w-0">
										<div class="text-sm font-medium text-slate-900">{{ $definition->label }}</div>
										<div class="text-xs text-slate-500">code: {{ $definition->code }} | type: {{ $definition->field_type }}</div>
									</div>
									<x-admin.icon-button
										icon="trash"
										title="Удалить характеристику"
										variant="danger"
										wire:click="deleteAttributeDefinition({{ $definition->id }})"
										onclick="if(!confirm('Удалить эту характеристику из справочника?')){event.preventDefault();event.stopImmediatePropagation();}"
									/>
								</div>
							@endforeach
						</div>
					@else
						<div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600">
							Справочник пока пуст. Добавьте первую характеристику.
						</div>
					@endif
				</div>

				<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
					<div class="text-sm font-semibold text-slate-900">Значения характеристик товара</div>
					<div class="mt-1 text-xs text-slate-600">
						Заполняются для текущего товара. Экспорт CSV/Excel пока не включает эти поля.
					</div>

					@if (!$product)
						<div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600">
							Сначала сохраните товар на вкладке "Детали", затем здесь можно заполнить значения.
						</div>
					@elseif ($attributeDefinitions->isEmpty())
						<div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600">
							Нет доступных характеристик в справочнике.
						</div>
					@else
						<form wire:submit.prevent="saveAttributes" class="mt-4 space-y-4">
							@foreach ($attributeDefinitions as $definition)
								@if ($definition->field_type === 'text')
									<x-admin.input
										:label="$definition->label"
										type="textarea"
										wire:model.defer="attributeValues.{{ $definition->id }}"
										:error="$errors->first('attributeValues.' . $definition->id)"
									/>
								@else
									<x-admin.input
										:label="$definition->label . ' (' . $definition->field_type . ')'"
										type="text"
										wire:model.defer="attributeValues.{{ $definition->id }}"
										:error="$errors->first('attributeValues.' . $definition->id)"
									/>
								@endif
							@endforeach

							<div class="flex items-center gap-4">
								<x-admin.button variant="primary" type="submit">
									{{ __('common.save') }}
								</x-admin.button>
								<x-action-message class="text-sm text-slate-600" on="attributes-saved">
									{{ __('common.saved') }}
								</x-action-message>
							</div>
						</form>
					@endif
				</div>
			</div>
		</x-admin.card>
	@endif

	@if ($tab === 'suppliers')
		@if ($product)
			<x-admin.card :title="__('common.suppliers_for_product')">
				<div class="space-y-4">
					<div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
						<div class="lg:col-span-2">
							<x-admin.select
								:label="__('common.supplier')"
								wire:model="attachSupplierId"
								:error="$errors->first('attachSupplierId')"
							>
								<option value="0">-</option>
								@foreach ($suppliers as $s)
									<option value="{{ $s->id }}">{{ $s->name }}</option>
								@endforeach
							</x-admin.select>
						</div>

						<div>
							<x-admin.select
								:label="__('common.status')"
								wire:model="attachStatus"
								:error="$errors->first('attachStatus')"
							>
								<option value="primary">{{ __('common.status_primary') }}</option>
								<option value="reserve">{{ __('common.status_reserve') }}</option>
							</x-admin.select>
						</div>

						<div class="flex items-end">
							<x-admin.button variant="primary" wire:click="attach" class="w-full">
								{{ __('common.link') }}
							</x-admin.button>
						</div>

						<div class="lg:col-span-4">
							<x-admin.input
								:label="__('common.price_terms')"
								type="textarea"
								wire:model="attachTerms"
								:error="$errors->first('attachTerms')"
							/>
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

						@forelse ($product->suppliers as $s)
							<tr class="hover:bg-slate-50/70">
								<x-admin.td>
									<a class="font-medium text-slate-900 underline hover:text-slate-700" href="{{ route('admin.suppliers.show', $s) }}">
										{{ $s->name }}
									</a>
								</x-admin.td>

								<x-admin.td>
									<x-admin.select wire:model="pivotStatus.{{ $s->id }}" size="sm">
										<option value="primary">{{ __('common.status_primary') }}</option>
										<option value="reserve">{{ __('common.status_reserve') }}</option>
									</x-admin.select>
								</x-admin.td>

								<x-admin.td>
									<x-admin.input
										type="textarea"
										wire:model="pivotTerms.{{ $s->id }}"
										size="sm"
									/>
								</x-admin.td>

								<x-admin.td align="right" nowrap>
									<div class="inline-flex items-center gap-2">
										<x-admin.button variant="secondary" size="sm" wire:click="savePivot({{ $s->id }})">
											{{ __('common.save') }}
										</x-admin.button>
										<x-admin.icon-button
											icon="trash"
											:title="__('common.detach')"
											variant="danger"
											wire:click="detach({{ $s->id }})"
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
				</div>
			</x-admin.card>
		@endif
	@endif
</div>


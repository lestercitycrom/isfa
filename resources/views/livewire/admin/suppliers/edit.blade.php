<div class="space-y-6">
	<x-admin.page-header
		:title="$supplier ? __('common.editing_supplier') : __('common.creating_supplier')"
		:subtitle="$supplier ? __('common.editing_supplier_subtitle') : __('common.creating_supplier_subtitle')"
	>
		<x-slot name="actions">
			<x-admin.button variant="secondary" :href="route('admin.suppliers.index')">
				{{ __('common.back') }}
			</x-admin.button>
		</x-slot>
	</x-admin.page-header>

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
								@elseif ($supplier && $supplier->photo_path)
									<img src="{{ asset('storage/' . $supplier->photo_path) }}" alt="{{ __('common.photo') }}" class="h-64 w-full object-cover" />
								@else
									<div class="flex h-64 items-center justify-center bg-slate-100 text-sm font-medium text-slate-500">
										{{ __('common.photo_not_set') }}
									</div>
								@endif
							</div>

							<label for="supplier-photo" class="block cursor-pointer rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-center text-sm text-slate-600 transition hover:border-slate-400 hover:bg-slate-50">
								<span class="inline-flex items-center justify-center gap-2 font-semibold text-slate-800">
									<x-admin.icon name="upload" class="h-4 w-4" />
									{{ __('common.photo_upload_action') }}
								</span>
								<span class="block mt-1 text-xs">{{ __('common.photo_upload_hint') }}</span>
							</label>
							<input id="supplier-photo" type="file" wire:model.live="photo" accept="image/*" class="sr-only" />

							<div wire:loading wire:target="photo" class="text-xs font-medium text-slate-500">
								{{ __('common.photo_uploading') }}
							</div>

							@if ($errors->has('photo'))
								<div class="text-xs text-red-600">{{ $errors->first('photo') }}</div>
							@endif

							@if ($photo || ($supplier && $supplier->photo_path))
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

					<x-admin.input
						:label="__('common.name')"
						type="text"
						wire:model="name"
						required
						autofocus
						:error="$errors->first('name')"
					/>

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

						<x-admin.input
							:label="__('common.email')"
							type="email"
							wire:model="email"
							:error="$errors->first('email')"
						/>

						<x-admin.input
							:label="__('common.website')"
							type="text"
							wire:model="website"
							:error="$errors->first('website')"
						/>
					</div>

					<x-admin.input
						:label="__('common.comment')"
						type="textarea"
						wire:model="comment"
						:error="$errors->first('comment')"
					/>
				</div>
			</div>

			<div class="flex items-center gap-4">
				<x-admin.button variant="primary" type="submit">
					{{ __('common.save') }}
				</x-admin.button>
				@if ($supplier)
					<x-admin.button variant="secondary" :href="route('admin.suppliers.show', $supplier)">
						{{ __('common.cancel') }}
					</x-admin.button>
				@endif
			</div>
		</form>
	</x-admin.card>
</div>

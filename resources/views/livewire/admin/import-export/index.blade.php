<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.import_export')"
		:subtitle="__('common.import_export_subtitle')"
	/>

	<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
		<x-admin.card :title="__('common.export_excel')">
			<div class="mt-3 space-y-3">
				<a class="block rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 transition" href="{{ route('admin.export.products.excel') }}">
					<x-admin.icon name="file-text" class="h-4 w-4 inline mr-2" />
					{{ __('common.download_products') }} (products.xlsx)
				</a>
				<a class="block rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 transition" href="{{ route('admin.export.suppliers.excel') }}">
					<x-admin.icon name="file-text" class="h-4 w-4 inline mr-2" />
					{{ __('common.download_suppliers') }} (suppliers.xlsx)
				</a>
				<a class="block rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 transition" href="{{ route('admin.export.links.excel') }}">
					<x-admin.icon name="file-text" class="h-4 w-4 inline mr-2" />
					{{ __('common.download_links') }} (product_supplier.xlsx)
				</a>
			</div>

			<div class="mt-4 text-xs text-slate-500">
				{{ __('common.import_accepts_same_columns') }}
			</div>
		</x-admin.card>
	</div>
</div>

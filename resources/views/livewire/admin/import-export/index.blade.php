<div class="space-y-6">
	<h1 class="text-2xl font-semibold">Импорт / Экспорт</h1>

	<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
		<div class="rounded border bg-white p-4 space-y-3">
			<div class="text-lg font-semibold">Экспорт CSV</div>

			<div class="space-y-2">
				<a class="block rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.export.products') }}">Скачать товары (products.csv)</a>
				<a class="block rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.export.suppliers') }}">Скачать поставщиков (suppliers.csv)</a>
				<a class="block rounded border px-3 py-2 hover:bg-zinc-50" href="{{ route('admin.export.links') }}">Скачать связи (product_supplier.csv)</a>
			</div>

			<div class="text-sm text-zinc-600">
				Импорт принимает те же колонки, что экспорт.
			</div>
		</div>

		<div class="rounded border bg-white p-4 space-y-6">
			<div>
				<div class="text-lg font-semibold mb-2">Импорт товаров CSV</div>
				<form class="space-y-2" method="POST" action="{{ route('admin.import.products') }}" enctype="multipart/form-data">
					@csrf
					<input class="block w-full" type="file" name="file" accept=".csv,text/csv">
					<button class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800" type="submit">Импорт</button>
				</form>
			</div>

			<div>
				<div class="text-lg font-semibold mb-2">Импорт поставщиков CSV</div>
				<form class="space-y-2" method="POST" action="{{ route('admin.import.suppliers') }}" enctype="multipart/form-data">
					@csrf
					<input class="block w-full" type="file" name="file" accept=".csv,text/csv">
					<button class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800" type="submit">Импорт</button>
				</form>
			</div>

			<div>
				<div class="text-lg font-semibold mb-2">Импорт связей CSV</div>
				<form class="space-y-2" method="POST" action="{{ route('admin.import.links') }}" enctype="multipart/form-data">
					@csrf
					<input class="block w-full" type="file" name="file" accept=".csv,text/csv">
					<button class="rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800" type="submit">Импорт</button>
				</form>
			</div>
		</div>
	</div>
</div>

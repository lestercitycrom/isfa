<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ $title ?? 'Admin' }}</title>

	@vite(['resources/css/app.css', 'resources/js/app.js'])
	@livewireStyles
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900">
	<div class="flex min-h-screen">
		<aside class="w-64 border-r bg-white">
			<div class="p-4 text-lg font-semibold">
				Справочник поставщиков
			</div>

			<nav class="px-2 pb-4 space-y-1">
				<a class="block rounded px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.products.index') }}">Товары</a>
				<a class="block rounded px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.suppliers.index') }}">Поставщики</a>
				<a class="block rounded px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.categories.index') }}">Категории</a>
				<a class="block rounded px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.import_export.index') }}">Импорт / Экспорт</a>
			</nav>

			<div class="px-4 pb-4">
				<form method="POST" action="{{ route('logout') }}">
					@csrf
					<button type="submit" class="w-full rounded bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-800">
						Выйти
					</button>
				</form>
			</div>
		</aside>

		<main class="flex-1 p-6">
			@if (session('status'))
				<div class="mb-4 rounded border border-emerald-200 bg-emerald-50 p-3 text-emerald-900">
					{{ session('status') }}
				</div>
			@endif

			{{ $slot }}
		</main>
	</div>

	@livewireScripts
</body>
</html>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	@vite(['resources/css/app.css', 'resources/js/app.js'])
	@livewireStyles

	<link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
	<link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
	<link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

	<title>@yield('title', config('app.name', 'Admin'))</title>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
	<header class="sticky top-0 z-40 bg-gradient-to-r from-slate-900 to-slate-800 text-slate-100 border-b border-white/10">
		<div class="mx-auto {{ config('admin-kit.layout.container', 'max-w-7xl') }} px-4">
			<div class="h-16 flex items-center justify-between gap-4">
				<a href="{{ route('admin.products.index') }}" class="flex items-center gap-2">
					<img src="{{ asset('favicon.png') }}" alt="{{ config('app.name') }}" class="h-9 w-9 rounded-xl">
					<span class="font-semibold tracking-wide">{{ config('app.name') }}</span>
				</a>

				<nav class="hidden md:flex items-center gap-1">
					@foreach((array) config('admin-kit.nav', []) as $item)
						@php
							$user = auth()->user();
							$route = (string) ($item['route'] ?? '');
							$labelKey = (string) ($item['label'] ?? '');
							$label = str_starts_with($labelKey, 'common.') ? __($labelKey) : $labelKey;
							$icon = (string) ($item['icon'] ?? '');
							$adminOnly = (bool) ($item['admin_only'] ?? false);
						@endphp

						@if($adminOnly && (!$user || !$user->isAdmin()))
							@continue
						@endif

						@if($route !== '' && $label !== '' && \Illuminate\Support\Facades\Route::has($route))
							@php $isActive = request()->routeIs($route); @endphp
							<a
								href="{{ route($route) }}"
								class="rounded-xl px-3 py-2 text-sm font-semibold transition inline-flex items-center gap-2
									{{ $isActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
							>
								@if($icon !== '')
									<x-admin.icon :name="$icon" class="h-4 w-4" />
								@endif
								<span>{{ $label }}</span>
							</a>
						@endif
					@endforeach
				</nav>

				<div class="flex items-center gap-3">
					<a class="hidden sm:flex items-center gap-2 text-sm text-slate-200 hover:text-white"
						href="{{ route('profile.edit') }}">
						<span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white font-semibold">
							{{ strtoupper(substr((string) auth()->user()?->name, 0, 1)) }}
						</span>
						<span class="font-medium">{{ auth()->user()?->name }}</span>
					</a>

					@if(\Illuminate\Support\Facades\Route::has('logout'))
						<form method="POST" action="{{ route('logout') }}">
							@csrf
							<button
								type="submit"
								class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold
									bg-white text-slate-900 hover:bg-slate-100 active:bg-slate-200"
							>
								{{ __('common.logout') }}
							</button>
						</form>
					@endif
				</div>
			</div>

			{{-- Mobile nav --}}
				<div class="md:hidden pb-3">
					<div class="flex flex-wrap gap-1">
						@foreach((array) config('admin-kit.nav', []) as $item)
							@php
								$user = auth()->user();
								$route = (string) ($item['route'] ?? '');
								$labelKey = (string) ($item['label'] ?? '');
								$label = str_starts_with($labelKey, 'common.') ? __($labelKey) : $labelKey;
								$icon = (string) ($item['icon'] ?? '');
								$adminOnly = (bool) ($item['admin_only'] ?? false);
							@endphp

							@if($adminOnly && (!$user || !$user->isAdmin()))
								@continue
							@endif

							@if($route !== '' && $label !== '' && \Illuminate\Support\Facades\Route::has($route))
								@php $isActive = request()->routeIs($route); @endphp
								<a
									href="{{ route($route) }}"
								class="rounded-xl px-3 py-2 text-sm font-semibold transition inline-flex items-center gap-2
									{{ $isActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
							>
								@if($icon !== '')
									<x-admin.icon :name="$icon" class="h-4 w-4" />
								@endif
								<span>{{ $label }}</span>
							</a>
						@endif
					@endforeach
				</div>
			</div>
		</div>
	</header>

	<main class="mx-auto {{ config('admin-kit.layout.container', 'max-w-7xl') }} px-4 py-6">
		@if (session('status'))
			<x-admin.alert variant="success" :autohide="true" class="mb-6">
				{{ session('status') }}
			</x-admin.alert>
		@endif

		{{ $slot ?? '' }}
		@yield('content')
	</main>

	@livewireScripts
</body>
</html>

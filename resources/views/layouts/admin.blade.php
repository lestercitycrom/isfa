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
	@php
		$initialToast = session('toast');
		if ($initialToast === null && session('status')) {
			$initialToast = [
				'type' => 'success',
				'message' => session('status'),
				'timeout' => 3500,
			];
		}

		$eventToMessage = [
			'comment-saved' => __('common.saved'),
			'attributes-saved' => __('common.saved'),
			'profile-updated' => __('common.profile_updated'),
			'password-updated' => __('common.password_updated'),
			'company-updated' => __('common.company_saved'),
			'keyword-subscription-saved' => __('common.saved'),
			'templates-saved' => __('common.saved'),
			'test-email-sent' => __('common.test_email_sent_ok'),
		];
	@endphp

	<header class="sticky top-0 z-40 bg-gradient-to-r from-slate-900 to-slate-800 text-slate-100 border-b border-white/10">
		<div class="mx-auto {{ config('admin-kit.layout.container', 'max-w-7xl') }} px-4">
			<div class="h-16 flex items-center justify-between gap-4">
				<a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
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
							$companyOnly = (bool) ($item['company_only'] ?? false);
							$children = is_array($item['children'] ?? null) ? $item['children'] : [];
						@endphp

						@if($adminOnly && (!$user || !$user->isAdmin()))
							@continue
						@endif
						@if($companyOnly && (!$user || $user->isAdmin()))
							@continue
						@endif

						@if(!empty($children))
							@php
								$visibleChildren = collect($children)->filter(function ($child) use ($user): bool {
									$childAdminOnly = (bool) ($child['admin_only'] ?? false);
									$childCompanyOnly = (bool) ($child['company_only'] ?? false);
									if ($childAdminOnly && (!$user || !$user->isAdmin())) {
										return false;
									}
									if ($childCompanyOnly && (!$user || $user->isAdmin())) {
										return false;
									}

									$childRoute = (string) ($child['route'] ?? '');

									return $childRoute !== '' && \Illuminate\Support\Facades\Route::has($childRoute);
								})->values();
								$childActive = $visibleChildren->contains(fn ($child): bool => request()->routeIs((string) $child['route']));
								$parentActive = ($route !== '' && request()->routeIs($route)) || $childActive;
							@endphp

							<div
								class="relative group"
								x-data="{ open: false }"
								x-on:click.outside="open = false"
								x-on:keydown.escape.window="open = false"
							>
								<button
									type="button"
									x-on:click="open = !open"
									class="list-none cursor-pointer rounded-xl px-3 py-2 text-sm font-semibold transition inline-flex items-center gap-2
										{{ $parentActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
									aria-haspopup="menu"
									x-bind:aria-expanded="open ? 'true' : 'false'"
								>
									@if($icon !== '')
										<x-admin.icon :name="$icon" class="h-4 w-4" />
									@endif
									<span>{{ $label }}</span>
									<x-admin.icon name="chevron-down" class="h-4 w-4" />
								</button>
								<div
									x-show="open"
									x-transition.origin.top.left
									class="absolute left-0 mt-2 min-w-52 rounded-xl border border-slate-200 bg-white p-1 shadow-lg z-50"
									role="menu"
								>
									@if($route !== '' && \Illuminate\Support\Facades\Route::has($route))
										<a href="{{ route($route) }}" x-on:click="open = false" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
											<x-admin.icon name="box" class="h-4 w-4 text-slate-500" />
											<span>{{ __('common.all_products') }}</span>
										</a>
									@endif
									@foreach($visibleChildren as $child)
										@php
											$childRoute = (string) ($child['route'] ?? '');
											$childLabelKey = (string) ($child['label'] ?? '');
											$childLabel = str_starts_with($childLabelKey, 'common.') ? __($childLabelKey) : $childLabelKey;
											$childIcon = (string) ($child['icon'] ?? '');
											$childIsActive = request()->routeIs($childRoute);
										@endphp
										<a href="{{ route($childRoute) }}" x-on:click="open = false" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm {{ $childIsActive ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50' }}">
											@if($childIcon !== '')
												<x-admin.icon :name="$childIcon" class="h-4 w-4 text-slate-500" />
											@endif
											<span>{{ $childLabel }}</span>
										</a>
									@endforeach
								</div>
							</div>
						@elseif($route !== '' && $label !== '' && \Illuminate\Support\Facades\Route::has($route))
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
								$companyOnly = (bool) ($item['company_only'] ?? false);
								$children = is_array($item['children'] ?? null) ? $item['children'] : [];
							@endphp

							@if($adminOnly && (!$user || !$user->isAdmin()))
								@continue
							@endif
							@if($companyOnly && (!$user || $user->isAdmin()))
								@continue
							@endif

						@if(!empty($children))
							@php
								$visibleChildren = collect($children)->filter(function ($child) use ($user): bool {
									$childAdminOnly = (bool) ($child['admin_only'] ?? false);
									$childCompanyOnly = (bool) ($child['company_only'] ?? false);
									if ($childAdminOnly && (!$user || !$user->isAdmin())) {
										return false;
									}
									if ($childCompanyOnly && (!$user || $user->isAdmin())) {
										return false;
									}
									$childRoute = (string) ($child['route'] ?? '');
									return $childRoute !== '' && \Illuminate\Support\Facades\Route::has($childRoute);
								})->values();
							@endphp

							<div class="w-full rounded-xl border border-white/10 bg-white/5 p-2">
								<div class="px-2 py-1 text-xs uppercase tracking-wide text-slate-300">{{ $label }}</div>
								@if($route !== '' && \Illuminate\Support\Facades\Route::has($route))
									<a href="{{ route($route) }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
										<x-admin.icon name="box" class="h-4 w-4" />
										<span>{{ __('common.all_products') }}</span>
									</a>
								@endif
								@foreach($visibleChildren as $child)
									@php
										$childRoute = (string) ($child['route'] ?? '');
										$childLabelKey = (string) ($child['label'] ?? '');
										$childLabel = str_starts_with($childLabelKey, 'common.') ? __($childLabelKey) : $childLabelKey;
										$childIcon = (string) ($child['icon'] ?? '');
										$childIsActive = request()->routeIs($childRoute);
									@endphp
									<a
										href="{{ route($childRoute) }}"
										class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm {{ $childIsActive ? 'bg-white/10 text-white' : 'text-slate-200 hover:bg-white/10' }}"
									>
										@if($childIcon !== '')
											<x-admin.icon :name="$childIcon" class="h-4 w-4" />
										@endif
										<span>{{ $childLabel }}</span>
									</a>
								@endforeach
							</div>
						@elseif($route !== '' && $label !== '' && \Illuminate\Support\Facades\Route::has($route))
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
		{{ $slot ?? '' }}
		@yield('content')
	</main>

	<x-admin.toast-stack :initial-toast="$initialToast" :event-to-message="$eventToMessage" />

	@livewireScripts
</body>
</html>

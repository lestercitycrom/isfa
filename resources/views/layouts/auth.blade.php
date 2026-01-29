<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
	<link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
	<link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

	<title>{{ $title ?? __('login.title') }} — {{ config('app.name', 'Admin') }}</title>

	@vite(['resources/css/app.css', 'resources/js/app.js'])
	@livewireStyles
</head>
<body class="min-h-screen bg-slate-50">
	<div class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
		<div class="w-full max-w-md space-y-8">
			{{ $slot }}
		</div>
	</div>

	@livewireScripts
</body>
</html>

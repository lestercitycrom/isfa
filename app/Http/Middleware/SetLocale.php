<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
	public function handle(Request $request, Closure $next): Response
	{
		$lang = $request->query('lang');
		if (in_array($lang, ['az', 'ru'], true)) {
			App::setLocale($lang);
			session()->put('locale', $lang);
		} elseif (session()->has('locale')) {
			App::setLocale(session()->get('locale'));
		} else {
			// Устанавливаем локаль по умолчанию из конфига
			$defaultLocale = config('app.locale', 'az');
			App::setLocale($defaultLocale);
		}

		return $next($request);
	}
}

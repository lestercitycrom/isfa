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
			// Если есть явный параметр lang в URL, используем его и сохраняем в сессию
			App::setLocale($lang);
			session()->put('locale', $lang);
		} else {
			// Если нет параметра lang, всегда используем локаль по умолчанию из конфига
			// Это гарантирует, что по умолчанию будет использоваться APP_LOCALE из .env
			$defaultLocale = config('app.locale', 'az');
			App::setLocale($defaultLocale);
		}

		return $next($request);
	}
}

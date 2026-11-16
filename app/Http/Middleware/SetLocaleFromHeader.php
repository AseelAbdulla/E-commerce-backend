<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', 'ar');

        $supportedLocales = ['ar', 'en'];
        $locale = in_array($locale, $supportedLocales) ? $locale : 'ar';

        App::setLocale($locale);

        return $next($request);
    }
}

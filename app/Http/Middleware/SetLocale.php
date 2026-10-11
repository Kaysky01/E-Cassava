<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang')
            ?? $request->session()->get('locale')
            ?? $request->cookie('locale', config('app.locale'));

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        $response = $next($request);

        // Controller boleh mengubah session locale (cth: /lang/{locale});
        // cookie selalu disinkronkan ke nilai akhir session.
        $final = $request->session()->get('locale', $locale);

        if (! in_array($final, ['id', 'en'], true)) {
            $final = 'id';
        }

        $response->headers->setCookie(cookie()->forever('locale', $final));

        return $response;
    }
}

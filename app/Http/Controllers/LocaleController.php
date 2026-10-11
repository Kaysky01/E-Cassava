<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        $request->session()->put('locale', $locale);

        $response = redirect()->back(fallback: '/');
        $response->headers->setCookie(cookie()->forever('locale', $locale));

        return $response;
    }
}

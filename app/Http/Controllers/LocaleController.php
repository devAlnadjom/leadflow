<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Persist the preferred locale in session and cookie.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:en,fr'],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);

        app()->setLocale($locale);

        return back()->withCookie(cookie()->forever('locale', $locale));
    }
}

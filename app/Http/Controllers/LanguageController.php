<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     */
    public function switch(Request $request, string $locale)
    {
        $available = array_keys(config('app.available_locales', []));

        if (!in_array($locale, $available, true)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Invalid locale'], 400);
            }
            return redirect()->back();
        }

        // Set locale in multiple places to ensure persistence
        Session::put('locale', $locale);
        App::setLocale($locale);
        \Carbon\Carbon::setLocale($locale);
        Cookie::queue('locale', $locale, 60 * 24 * 365);

        // Save to user preference if logged in
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'locale'  => $locale,
                'message' => __('messages.language_changed'),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Get all available languages.
     */
    public function available()
    {
        return response()->json(config('app.available_locales', []));
    }

    /**
     * Get current language details.
     */
    public function current()
    {
        $locale = App::getLocale();
        return response()->json([
            'locale' => $locale,
            'config' => config("app.available_locales.{$locale}", []),
        ]);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('app.available_locales'));
        $locale = null;

        // 1. Try subdomain (en.example.com, ar.example.com ...)
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        if (count($parts) > 2) {
            $sub = strtolower($parts[0]);
            if (in_array($sub, $available)) {
                $locale = $sub;
            }
        }

        // 2. Fallback to session
        if (!$locale && Session::has('locale') && in_array(Session::get('locale'), $available)) {
            $locale = Session::get('locale');
        }

        // 3. Fallback to browser Accept-Language (optional)
        if (!$locale) {
            $locale = $request->getPreferredLanguage($available) ?? config('app.fallback_locale');
        }

        // Final safety
        if (!in_array($locale, $available)) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        // Share direction (LTR/RTL) with all views
        view()->share('currentLocale', $locale);
        view()->share('textDirection', config("app.available_locales.$locale.dir", 'ltr'));

        return $next($request);
    }
}
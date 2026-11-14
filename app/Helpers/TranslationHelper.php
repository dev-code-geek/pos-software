<?php

use Illuminate\Support\Facades\App;

if (!function_exists('t')) {
    /**
     * Translate the given message with optional parameters.
     */
    function t(string $key, array $replace = [], ?string $locale = null): string
    {
        return __($key, $replace, $locale);
    }
}

if (!function_exists('current_locale')) {
    /**
     * Get the current application locale.
     */
    function current_locale(): string
    {
        return App::getLocale();
    }
}

if (!function_exists('current_language')) {
    /**
     * Get the current language configuration.
     */
    function current_language(): array
    {
        return config('app.available_locales.' . App::getLocale(), [
            'name'   => 'English',
            'native' => 'English',
            'flag'   => '🇬🇧',
            'dir'    => 'ltr',
        ]);
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Check if the current locale is RTL.
     */
    function is_rtl(): bool
    {
        return (current_language()['dir'] ?? 'ltr') === 'rtl';
    }
}

if (!function_exists('available_locales')) {
    /**
     * Get all available locales.
     */
    function available_locales(): array
    {
        return config('app.available_locales', []);
    }
}

if (!function_exists('format_number')) {
    /**
     * Format a number according to the current locale.
     */
    function format_number($value, int $decimals = 2): string
    {
        $decimalSep = t('messages.decimal_separator') ?: '.';
        $thousandsSep = t('messages.thousands_separator') ?: ',';
        return number_format((float) $value, $decimals, $decimalSep, $thousandsSep);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency with symbol.
     */
    function format_currency($value, ?string $symbol = null, int $decimals = 2): string
    {
        $symbol = $symbol ?? 'TSh';
        return $symbol . ' ' . format_number($value, $decimals);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date according to current locale.
     */
    function format_date($date, string $format = 'd M Y'): string
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->locale(current_locale())->translatedFormat($format);
    }
}

if (!function_exists('language_switcher_url')) {
    /**
     * Get URL to switch language.
     */
    function language_switcher_url(string $locale): string
    {
        return route('language.switch', $locale);
    }
}
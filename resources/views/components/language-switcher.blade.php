@php
    $locales = [
        'en' => 'English',
        'ar' => 'العربية',
        'fr' => 'Français',
        'sw' => 'Kiswahili',
    ];
    $current = app()->getLocale();
@endphp

<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        {{ $locales[$current] ?? 'English' }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach($locales as $code => $name)
            <li>
                <a class="dropdown-item {{ $code === $current ? 'active' : '' }}" 
                   href="{{ route('lang.switch', $code) }}">
                    {{ $name }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
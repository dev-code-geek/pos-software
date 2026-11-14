<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $textDirection ?? 'ltr' }}">
<head>

    @if(($textDirection ?? 'ltr') === 'rtl')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ICT Inventory')</title>
    
    @include('layouts.partials.admin-styles')
    @yield('styles')
    
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <div id="preloader">
        <div class="loader"></div>
    </div>

    <div class="page-container">
        @include('layouts.partials.admin-sidebar')

        <div class="main-content">
            @include('layouts.partials.admin-header')
            
            <div class="content-wrapper">
                <div class="container-fluid px-0">
                    @yield('admin-content')
                </div>
            </div>
            
            @include('layouts.partials.admin-footer')
        </div>
    </div>

    @include('layouts.partials.admin-scripts')

    {{--
        FIX (root cause of "buttons don't work, no console errors"):
        every index view pushes its page-specific JS with @push('scripts') /
        @endpush. That writes into a named STACK, not a section. @yield()
        only reads @section()/@endsection content, so it was always
        resolving to nothing here — every pushed <script> block across the
        whole admin panel was silently dropped and never reached the
        browser. Buttons rendered fine (plain HTML) but had zero JS
        listeners attached, hence "no errors": there was no script to
        error out in the first place.

        @stack('scripts') is the correct directive to render everything
        that was queued via @push('scripts').
    --}}
    @stack('scripts')
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        });
    </script>
    @endif
</body>

</html>
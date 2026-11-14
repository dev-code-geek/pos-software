<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'User Dashboard') - ICT Inventory</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --sidebar-bg: #0f172a;
            --sidebar-dark: #020617;
            --sidebar-hover: #1e293b;
            --sidebar-active: #4f46e5;
            --sidebar-text: #e2e8f0;
            --sidebar-text-muted: #94a3b8;
            --sidebar-border: #334155;
            --sidebar-width: 260px;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f1f5f9;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Page Container */
        .page-container {
            display: flex;
            min-height: 100vh;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            display: flex;
            flex-direction: column;
            max-width: calc(100% - var(--sidebar-width));
        }

        .content-wrapper {
            flex: 1;
            padding: 20px 25px;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .main-content {
                margin-left: 0 !important;
                max-width: 100% !important;
            }
            .content-wrapper {
                padding: 15px;
            }
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 10px;
            }
        }

        /* Preloader */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #preloader .loader {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: all 0.25s ease;
            margin-bottom: 16px;
            overflow: hidden;
            background: #fff;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }

        .card-header {
            background: #fafbfc;
            border-bottom: 1px solid #eef2f7;
            padding: 12px 18px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .card-header .card-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
            color: #0f172a;
        }

        .card-body {
            padding: 12px 18px;
        }

        .card-footer {
            background: #fafbfc;
            border-top: 1px solid #eef2f7;
            padding: 10px 18px;
        }

        /* Stats Cards */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
            margin: 0;
        }

        /* Quick Action Cards */
        .quick-action-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.25s ease;
            border: 1px solid #eef2f7;
            text-decoration: none;
            display: block;
            height: 100%;
        }

        .quick-action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }

        .quick-action-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .quick-action-card h6 {
            font-weight: 600;
            color: #0f172a;
            margin: 0 0 3px;
            font-size: 0.9rem;
        }

        .quick-action-card p {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0;
        }

        /* Badge Status */
        .badge-status {
            font-size: 0.7rem;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }
        .badge-status.pending { background: #fef3c7; color: #92400e; }
        .badge-status.reviewing { background: #dbeafe; color: #1e40af; }
        .badge-status.approved { background: #d1fae5; color: #065f46; }
        .badge-status.rejected { background: #fee2e2; color: #991b1b; }
        .badge-status.assigned { background: #d1fae5; color: #065f46; }
        .badge-status.completed { background: #e5e7eb; color: #374151; }

        /* Avatar */
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.7rem;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    
    @yield('styles')
</head>

<body>
    <div id="preloader">
        <div class="loader"></div>
    </div>

    <div class="page-container">
        @include('layouts.partials.user-sidebar')

        <div class="main-content">
            @include('layouts.partials.user-header')
            
            <div class="content-wrapper">
                <div class="container-fluid px-0">
                    @yield('user-content')
                </div>
            </div>
            
            @include('layouts.partials.user-footer')
        </div>
    </div>

    @include('layouts.partials.user-scripts')
    @yield('scripts')
    
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
    
    @if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'info',
                title: 'Information',
                text: '{{ session('info') }}',
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
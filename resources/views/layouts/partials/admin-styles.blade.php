<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
    --sidebar-collapsed-width: 70px;
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

body.sidebar-collapsed .main-content {
    margin-left: var(--sidebar-collapsed-width);
    max-width: calc(100% - var(--sidebar-collapsed-width));
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
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.2s ease;
    margin-bottom: 16px;
    overflow: hidden;
    background: #fff;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.card-header {
    background: transparent;
    border-bottom: 1px solid #eef2f7;
    padding: 12px 16px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.card-header .card-title {
    font-size: 0.95rem;
    margin: 0;
}

.card-body {
    padding: 12px 16px;
}

.card-footer {
    background: transparent;
    border-top: 1px solid #eef2f7;
    padding: 10px 16px;
}

/* Small Box Stats */
.small-box {
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.2s ease;
    padding: 15px 18px;
    position: relative;
    overflow: hidden;
    color: #fff;
    min-height: 80px;
}

.small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.small-box .inner {
    padding: 3px 0;
}

.small-box .inner h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 2px;
}

.small-box .inner p {
    font-size: 0.8rem;
    margin: 0;
    opacity: 0.8;
}

.small-box .icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 2rem;
    opacity: 0.2;
}

.small-box .small-box-footer {
    display: block;
    padding: 4px 0;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.75rem;
}

.small-box .small-box-footer:hover {
    color: #fff;
    transform: translateX(5px);
}

.small-box.bg-primary { background: #4f46e5; }
.small-box.bg-success { background: #22c55e; }
.small-box.bg-warning { background: #f59e0b; }
.small-box.bg-danger { background: #ef4444; }
.small-box.bg-info { background: #3b82f6; }
.small-box.bg-secondary { background: #64748b; }

/* Quick Action Cards */
.quick-action-card {
    background: #fff;
    border-radius: 8px;
    padding: 14px 12px;
    text-align: center;
    transition: all 0.2s ease;
    border: 1px solid #eef2f7;
    text-decoration: none;
    display: block;
    height: 100%;
}

.quick-action-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.quick-action-card i {
    font-size: 1.5rem;
    margin-bottom: 8px;
    display: block;
}

.quick-action-card h6 {
    font-weight: 600;
    color: #1a1a2e;
    margin: 0 0 2px;
    font-size: 0.85rem;
}

.quick-action-card p {
    font-size: 0.7rem;
    color: #6c757d;
    margin: 0;
}

/* Table Enhancements */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    margin-bottom: 0;
    font-size: 0.8rem;
}

.table th {
    font-weight: 600;
    color: #1a1a2e;
    border-bottom: 2px solid #eef2f7;
    white-space: nowrap;
    padding: 8px 10px;
}

.table td {
    vertical-align: middle;
    padding: 8px 10px;
}

.table-hover tbody tr:hover {
    background-color: #f8fafc;
}

/* Badge */
.badge {
    font-weight: 500;
    padding: 3px 8px;
    font-size: 0.7rem;
}

/* Toast Container */
#toast-container {
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

/* Modal Fixes */
.modal-dialog {
    max-width: 95%;
    margin: 1.5rem auto;
}

.modal-content {
    border-radius: 8px;
    border: none;
}

.modal-header {
    padding: 12px 16px;
    border-bottom: 1px solid #eef2f7;
}

.modal-body {
    padding: 16px;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 10px 16px;
    border-top: 1px solid #eef2f7;
}

/* Form Controls */
.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    padding: 6px 10px;
    font-size: 0.8rem;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Button Sizes */
.btn-sm {
    padding: 3px 8px;
    font-size: 0.7rem;
}

.btn-group-sm > .btn {
    padding: 3px 6px;
    font-size: 0.65rem;
}

/* Pagination */
.pagination {
    margin: 0;
}

.pagination .page-link {
    padding: 4px 10px;
    font-size: 0.75rem;
    border-radius: 4px;
}

/* Input Group */
.input-group-sm > .form-control,
.input-group-sm > .input-group-append > .btn {
    padding: 4px 8px;
    font-size: 0.75rem;
}

/* Alert */
.alert {
    padding: 10px 15px;
    font-size: 0.85rem;
    border-radius: 6px;
}
</style>
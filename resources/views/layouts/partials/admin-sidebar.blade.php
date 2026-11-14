<style>
/* ============================================
   SIDEBAR STYLES
============================================ */
:root {
    --sidebar-width: 260px;
    --sidebar-collapsed-width: 70px;
    --sidebar-bg: #0f172a;
    --sidebar-dark: #0b1120;
    --sidebar-text: #cbd5e1;
    --sidebar-text-muted: #64748b;
    --sidebar-hover: rgba(255,255,255,0.06);
    --sidebar-border: rgba(255,255,255,0.06);
    --primary-color: #4f46e5;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
}

.ict-sidebar {
    width: var(--sidebar-width);
    background: var(--sidebar-bg);
    color: var(--sidebar-text);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    z-index: 1000;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 4px 0 20px rgba(0,0,0,0.1);
}

.ict-sidebar-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px 12px;
}
.ict-sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.ict-sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.ict-sidebar-scroll::-webkit-scrollbar-thumb {
    background: var(--sidebar-hover);
    border-radius: 4px;
}

/* Logo */
.ict-sidebar-logo {
    padding: 20px;
    background: var(--sidebar-dark);
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    gap: 12px;
}
.ict-logo-icon {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #fff;
    flex-shrink: 0;
}
.ict-logo-text h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}
.ict-logo-text span {
    font-size: 0.6rem;
    color: var(--sidebar-text-muted);
    display: block;
}

/* User Profile */
.ict-user-profile {
    padding: 20px 16px;
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    gap: 12px;
}
.ict-user-avatar {
    width: 48px;
    height: 48px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #fff;
    flex-shrink: 0;
    position: relative;
}
.ict-user-avatar::after {
    content: '';
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: var(--success-color);
    border-radius: 50%;
    border: 2px solid var(--sidebar-bg);
}
.ict-user-info h5 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
}
.ict-user-info p {
    margin: 4px 0 0;
    font-size: 0.7rem;
    color: var(--sidebar-text-muted);
}
.ict-role-badge {
    display: inline-block;
    background: rgba(79,70,229,0.2);
    color: #818cf8;
    font-size: 0.6rem;
    padding: 2px 8px;
    border-radius: 20px;
    margin-top: 5px;
    font-weight: 500;
}

/* Navigation */
.ict-nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
}
.ict-nav-category {
    padding: 16px 12px 8px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--sidebar-text-muted);
    border-bottom: 1px solid var(--sidebar-border);
    margin-bottom: 8px;
}
.ict-nav-item {
    margin-bottom: 2px;
}
.ict-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    position: relative;
}
.ict-nav-link:hover {
    background: var(--sidebar-hover);
    color: #fff;
}
.ict-nav-link.active {
    background: rgba(79,70,229,0.15);
    color: #818cf8;
}
.ict-nav-link.active .ict-nav-icon {
    color: #818cf8;
}
.ict-nav-icon {
    width: 22px;
    font-size: 1.1rem;
    text-align: center;
    flex-shrink: 0;
    color: var(--sidebar-text-muted);
}
.ict-nav-link:hover .ict-nav-icon {
    color: #fff;
}
.ict-nav-link.active .ict-nav-icon {
    color: #818cf8;
}
.ict-nav-text {
    flex: 1;
}

/* Dropdown */
.ict-has-dropdown {
    position: relative;
}
.ict-has-dropdown > .ict-nav-link {
    padding-right: 35px;
    cursor: pointer;
}
.ict-dropdown-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.7rem;
    transition: transform 0.25s ease;
    color: var(--sidebar-text-muted);
}
.ict-has-dropdown.open > .ict-nav-link .ict-dropdown-arrow {
    transform: translateY(-50%) rotate(90deg);
}
.ict-nav-submenu {
    list-style: none;
    margin: 0;
    padding: 0 0 0 20px;
    display: none;
}
.ict-nav-submenu.show {
    display: block;
}
.ict-nav-submenu .ict-nav-link {
    padding: 8px 12px;
    font-size: 0.8rem;
    padding-left: 40px;
}
.ict-nav-submenu .ict-nav-icon {
    width: 18px;
    font-size: 0.8rem;
    color: var(--sidebar-text-muted);
}

/* Badge */
.ict-nav-badge {
    background: var(--danger-color);
    color: #fff;
    font-size: 0.6rem;
    padding: 2px 8px;
    border-radius: 20px;
    margin-left: auto;
    font-weight: 600;
    min-width: 20px;
    text-align: center;
    line-height: 1.4;
}
.ict-nav-badge-success {
    background: var(--success-color);
}
.ict-nav-badge-warning {
    background: var(--warning-color);
    color: #1e293b;
}
.ict-nav-badge-info {
    background: var(--info-color);
}
.ict-nav-badge-secondary {
    background: #6b7280;
}

/* Footer */
.ict-sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--sidebar-border);
    background: var(--sidebar-dark);
}
.ict-footer-links {
    display: flex;
    justify-content: space-around;
}
.ict-footer-link {
    color: var(--sidebar-text-muted);
    text-decoration: none;
    font-size: 0.7rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.ict-footer-link:hover {
    color: #818cf8;
}

/* Toggle Button */
.ict-sidebar-toggle {
    position: fixed;
    left: var(--sidebar-width);
    top: 20px;
    width: 36px;
    height: 36px;
    background: var(--primary-color);
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1001;
    transition: all 0.3s ease;
    color: #fff;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.ict-sidebar-toggle:hover {
    background: #4338ca;
    transform: scale(1.05);
}
body.sidebar-collapsed .ict-sidebar-toggle {
    left: var(--sidebar-collapsed-width);
}

/* Mobile Overlay */
.ict-sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    display: none;
}

/* Collapsed styles */
body.sidebar-collapsed .ict-nav-text,
body.sidebar-collapsed .ict-dropdown-arrow,
body.sidebar-collapsed .ict-nav-badge {
    display: none;
}
body.sidebar-collapsed .ict-nav-link {
    justify-content: center;
    padding: 12px;
}
body.sidebar-collapsed .ict-nav-icon {
    margin-right: 0;
    font-size: 1.25rem;
}
body.sidebar-collapsed .ict-user-info,
body.sidebar-collapsed .ict-logo-text {
    display: none;
}
body.sidebar-collapsed .ict-user-avatar {
    margin: 0 auto;
}
body.sidebar-collapsed .ict-logo-icon {
    margin: 0 auto;
}
body.sidebar-collapsed .ict-user-profile {
    justify-content: center;
}
body.sidebar-collapsed .ict-footer-links span {
    display: none;
}

/* Mobile */
@media (max-width: 768px) {
    .ict-sidebar {
        transform: translateX(-100%);
        box-shadow: none;
    }
    body.sidebar-mobile-open .ict-sidebar {
        transform: translateX(0);
        box-shadow: 4px 0 20px rgba(0,0,0,0.2);
    }
    .ict-sidebar-toggle {
        display: flex;
    }
    .ict-sidebar-overlay.active {
        display: block;
    }
}

/* Animation for badge updates */
.ict-nav-badge.badge-updated {
    animation: badgePulse 0.5s ease;
}
@keyframes badgePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}
</style>

@php
    $user = auth()->user();
    $displayRole = $user->roles->first()->name ?? 'User';
    $isSuperAdmin = $user->hasRole('super_admin');
    $isAdmin = $user->hasRole('admin');
    $canViewAll = $isSuperAdmin || $isAdmin;
@endphp

<!-- Toggle Button -->
<button class="ict-sidebar-toggle" id="ictSidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Mobile Overlay -->
<div class="ict-sidebar-overlay" id="ictSidebarOverlay"></div>

<!-- Sidebar -->
<div class="ict-sidebar" id="ictSidebar">
    <!-- Logo -->
    <div class="ict-sidebar-logo">
        <div class="ict-logo-icon"><i class="fas fa-cubes"></i></div>
        <div class="ict-logo-text">
            <h2>AssetCore</h2>
            <span>Inventory & Financials</span>
        </div>
    </div>

    <!-- User Profile -->
    <div class="ict-user-profile">
        <div class="ict-user-avatar">
            <i class="fas fa-user"></i>
            @if($user->email_verified_at)
                <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-light rounded-circle" style="width:12px;height:12px;"></span>
            @endif
        </div>
        <div class="ict-user-info">
            <h5>{{ $user->name ?? 'Admin' }}</h5>
            <div class="ict-role-badge"><i class="fas fa-tag me-1"></i> {{ ucfirst($displayRole) }}</div>

            {{-- Language Switcher --}}
            <div class="mt-2">
                <x-language-switcher />
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="ict-sidebar-scroll">
        <ul class="ict-nav-menu">
            <!-- MAIN -->
            <li class="ict-nav-category">{{ __('messages.main') }}</li>

            {{-- Dashboard --}}
            @if($canViewAll || auth()->user()->can('dashboard.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="ict-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.dashboard') }}</span>
                    </a>
                </li>
            @endif

            <!-- INVENTORY -->
            <li class="ict-nav-category">{{ __('messages.inventory') }}</li>

            {{-- Products --}}
            @if($canViewAll || auth()->user()->can('product.view'))
                <li class="ict-nav-item ict-has-dropdown {{ request()->routeIs('admin.products.*') ? 'open' : '' }}">
                    <div class="ict-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" data-target="products-dropdown">
                        <i class="fas fa-box ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.products') }}</span>
                        <i class="fas fa-chevron-right ict-dropdown-arrow"></i>
                    </div>
                    <ul class="ict-nav-submenu {{ request()->routeIs('admin.products.*') ? 'show' : '' }}" id="products-dropdown">
                        <li>
                            <a href="{{ route('admin.products.index') }}" class="ict-nav-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                                <i class="fas fa-list ict-nav-icon"></i> {{ __('messages.all_products') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- Categories --}}
            @if($canViewAll || auth()->user()->can('category.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="ict-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.categories') }}</span>
                    </a>
                </li>
            @endif

            {{-- Warehouses --}}
            @if(Route::has('admin.warehouses.index') && ($canViewAll || auth()->user()->can('warehouse.view')))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.warehouses.index') }}" class="ict-nav-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                        <i class="fas fa-warehouse ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.warehouses') }}</span>
                    </a>
                </li>
            @endif

            {{-- Stock Adjustments --}}
            @if(Route::has('admin.stock-adjustments.index') && ($canViewAll || auth()->user()->can('stock_adjustment.view')))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.stock-adjustments.index') }}" class="ict-nav-link {{ request()->routeIs('admin.stock-adjustments.*') ? 'active' : '' }}">
                        <i class="fas fa-balance-scale ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.stock_adjustments') }}</span>
                    </a>
                </li>
            @endif

            {{-- Stock Transfers --}}
            @if(Route::has('admin.stock-transfers.index') && ($canViewAll || auth()->user()->can('stock_transfer.view')))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.stock-transfers.index') }}" class="ict-nav-link {{ request()->routeIs('admin.stock-transfers.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.stock_transfers') }}</span>
                    </a>
                </li>
            @endif

            {{-- Stock Movements --}}
            @if($canViewAll || auth()->user()->can('stock.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.stock-movements.index') }}" class="ict-nav-link {{ request()->routeIs('admin.stock-movements.*') ? 'active' : '' }}">
                        <i class="fas fa-arrows-alt-h ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.stock_movements') }}</span>
                    </a>
                </li>
            @endif

            <!-- PROCUREMENT -->
            <li class="ict-nav-category">{{ __('messages.procurement') }}</li>

            {{-- Suppliers --}}
            @if($canViewAll || auth()->user()->can('supplier.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.suppliers.index') }}" class="ict-nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.suppliers') }}</span>
                    </a>
                </li>
            @endif

            {{-- Purchase Orders --}}
            @if($canViewAll || auth()->user()->can('purchase_order.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="ict-nav-link {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.purchase_orders') }}</span>
                    </a>
                </li>
            @endif

            <!-- TRANSACTIONS -->
            <li class="ict-nav-category">{{ __('messages.transactions') }}</li>

            {{-- Quotations --}}
            @if(Route::has('admin.quotations.index') && ($canViewAll || auth()->user()->can('quotation.view')))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.quotations.index') }}" class="ict-nav-link {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.quotations') }}</span>
                    </a>
                </li>
            @endif

            {{-- Sales --}}
            @if(Route::has('admin.sales.index') && ($canViewAll || auth()->user()->can('sale.view')))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.sales.index') }}" class="ict-nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.sales') }}</span>
                    </a>
                </li>
            @endif

            <!-- PAYMENTS -->
            <li class="ict-nav-category">{{ __('messages.payments') }}</li>

            {{-- Purchase Payments --}}
            @if($canViewAll || auth()->user()->can('purchase_payment.view'))
                @if(Route::has('admin.purchase-payments.index'))
                    <li class="ict-nav-item">
                        <a href="{{ route('admin.purchase-payments.index') }}" class="ict-nav-link {{ request()->routeIs('admin.purchase-payments.*') ? 'active' : '' }}">
                            <i class="fas fa-arrow-right ict-nav-icon"></i>
                            <span class="ict-nav-text">{{ __('messages.purchase_payments') }}</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- Sales Payments --}}
            @if($canViewAll || auth()->user()->can('sales_payment.view'))
                @if(Route::has('admin.sales-payments.index'))
                    <li class="ict-nav-item">
                        <a href="{{ route('admin.sales-payments.index') }}" class="ict-nav-link {{ request()->routeIs('admin.sales-payments.*') ? 'active' : '' }}">
                            <i class="fas fa-arrow-left ict-nav-icon"></i>
                            <span class="ict-nav-text">{{ __('messages.sales_payments') }}</span>
                        </a>
                    </li>
                @endif
            @endif

            <!-- ORGANIZATION -->
            <li class="ict-nav-category">{{ __('messages.organization') }}</li>

            {{-- Companies --}}
            @if($canViewAll || auth()->user()->can('company.view'))
                @if(Route::has('admin.companies.index'))
                    <li class="ict-nav-item">
                        <a href="{{ route('admin.companies.index') }}" class="ict-nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                            <i class="fas fa-building ict-nav-icon"></i>
                            <span class="ict-nav-text">{{ __('messages.companies') }}</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- Branches --}}
            @if($canViewAll || auth()->user()->can('branch.view'))
                @if(Route::has('admin.branches.index'))
                    <li class="ict-nav-item">
                        <a href="{{ route('admin.branches.index') }}" class="ict-nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                            <i class="fas fa-store-alt ict-nav-icon"></i>
                            <span class="ict-nav-text">{{ __('messages.branches') }}</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- Customers --}}
            @if($canViewAll || auth()->user()->can('customer.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.customers.index') }}" class="ict-nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <i class="fas fa-users ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.customers') }}</span>
                    </a>
                </li>
            @endif

            {{-- Departments --}}
            @if($canViewAll || auth()->user()->can('department.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.departments.index') }}" class="ict-nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                        <i class="fas fa-building ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.departments') }}</span>
                    </a>
                </li>
            @endif

            <!-- HR & PAYROLL -->
            <li class="ict-nav-category">{{ __('messages.hr_payroll') }}</li>

            {{-- Employees --}}
            @if($canViewAll || auth()->user()->can('employee.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.employees.index') }}" class="ict-nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                        <i class="fas fa-users ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.employees') }}</span>
                    </a>
                </li>
            @endif

            {{-- Leave Management --}}
            @if(auth()->user()->can('leave_type.view') || auth()->user()->can('leave_balance.view') || auth()->user()->can('leave_request.view'))
                <li class="ict-nav-item ict-has-dropdown {{ request()->routeIs('admin.leave-types.*') || request()->routeIs('admin.leave.balances.*') || request()->routeIs('admin.leave.requests.*') ? 'open' : '' }}">
                    <div class="ict-nav-link {{ request()->routeIs('admin.leave-types.*') || request()->routeIs('admin.leave.balances.*') || request()->routeIs('admin.leave.requests.*') ? 'active' : '' }}" data-target="leave-dropdown">
                        <i class="fas fa-calendar-alt ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.leave_management') }}</span>
                        <i class="fas fa-chevron-right ict-dropdown-arrow"></i>
                    </div>
                    <ul class="ict-nav-submenu {{ request()->routeIs('admin.leave-types.*') || request()->routeIs('admin.leave.balances.*') || request()->routeIs('admin.leave.requests.*') ? 'show' : '' }}" id="leave-dropdown">
                        @can('leave_type.view')
                            <li>
                                <a href="{{ route('admin.leave-types.index') }}" class="ict-nav-link {{ request()->routeIs('admin.leave-types.*') ? 'active' : '' }}">
                                    <i class="fas fa-list ict-nav-icon"></i> {{ __('messages.leave_types') }}
                                </a>
                            </li>
                        @endcan
                        @can('leave_balance.view')
                            <li>
                                <a href="{{ route('admin.leave.balances.index') }}" class="ict-nav-link {{ request()->routeIs('admin.leave.balances.*') ? 'active' : '' }}">
                                    <i class="fas fa-balance-scale ict-nav-icon"></i> {{ __('messages.leave_balances') }}
                                </a>
                            </li>
                        @endcan
                        @can('leave_request.view')
                            <li>
                                <a href="{{ route('admin.leave.requests.index') }}" class="ict-nav-link {{ request()->routeIs('admin.leave.requests.*') ? 'active' : '' }}">
                                    <i class="fas fa-paper-plane ict-nav-icon"></i> {{ __('messages.leave_requests') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif

            {{-- Attendance --}}
            @if($canViewAll || auth()->user()->can('attendance.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.attendance.index') }}" class="ict-nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.attendance') }}</span>
                    </a>
                </li>
            @endif

            {{-- Payroll --}}
            @if($canViewAll || auth()->user()->can('payroll_period.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.payroll.index') }}" class="ict-nav-link {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.payroll') }}</span>
                    </a>
                </li>
            @endif

            <!-- ANALYTICS -->
            <li class="ict-nav-category">{{ __('messages.analytics') }}</li>

            {{-- Reports --}}
            @if($canViewAll || auth()->user()->can('report.view'))
                <li class="ict-nav-item ict-has-dropdown {{ request()->routeIs('admin.reports.*') ? 'open' : '' }}">
                    <div class="ict-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" data-target="reports-dropdown">
                        <i class="fas fa-chart-bar ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.reports') }}</span>
                        <i class="fas fa-chevron-right ict-dropdown-arrow"></i>
                    </div>
                    <ul class="ict-nav-submenu {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="reports-dropdown">
                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.inventory') }}</li>
                        @if(Route::has('admin.reports.inventory'))
                            <li><a href="{{ route('admin.reports.inventory') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }}"><i class="fas fa-boxes ict-nav-icon"></i> {{ __('messages.inventory_report') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.low-stock'))
                            <li><a href="{{ route('admin.reports.low-stock') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.low-stock') ? 'active' : '' }}"><i class="fas fa-exclamation-triangle ict-nav-icon"></i> {{ __('messages.low_stock') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.warranty-expiry'))
                            <li><a href="{{ route('admin.reports.warranty-expiry') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.warranty-expiry') ? 'active' : '' }}"><i class="fas fa-file-contract ict-nav-icon"></i> {{ __('messages.warranty_expiry') }}</a></li>
                        @endif

                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.organization') }}</li>
                        @if(Route::has('admin.reports.companies'))
                            <li><a href="{{ route('admin.reports.companies') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.companies') ? 'active' : '' }}"><i class="fas fa-building ict-nav-icon"></i> {{ __('messages.companies') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.branches'))
                            <li><a href="{{ route('admin.reports.branches') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.branches') ? 'active' : '' }}"><i class="fas fa-store-alt ict-nav-icon"></i> {{ __('messages.branches') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.departments'))
                            <li><a href="{{ route('admin.reports.departments') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.departments') ? 'active' : '' }}"><i class="fas fa-sitemap ict-nav-icon"></i> {{ __('messages.departments') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.warehouses'))
                            <li><a href="{{ route('admin.reports.warehouses') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.warehouses') ? 'active' : '' }}"><i class="fas fa-warehouse ict-nav-icon"></i> {{ __('messages.warehouses') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.categories'))
                            <li><a href="{{ route('admin.reports.categories') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.categories') ? 'active' : '' }}"><i class="fas fa-tags ict-nav-icon"></i> {{ __('messages.categories') }}</a></li>
                        @endif

                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.hr') }}</li>
                        @if(Route::has('admin.reports.employees'))
                            <li><a href="{{ route('admin.reports.employees') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.employees') ? 'active' : '' }}"><i class="fas fa-users ict-nav-icon"></i> {{ __('messages.employees') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.attendance'))
                            <li><a href="{{ route('admin.reports.attendance') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.attendance') ? 'active' : '' }}"><i class="fas fa-clipboard-check ict-nav-icon"></i> {{ __('messages.attendance') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.leave'))
                            <li><a href="{{ route('admin.reports.leave') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.leave') ? 'active' : '' }}"><i class="fas fa-calendar-alt ict-nav-icon"></i> {{ __('messages.leave_reports') }}</a></li>
                        @endif

                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.payroll') }}</li>
                        @if(Route::has('admin.reports.payroll'))
                            <li><a href="{{ route('admin.reports.payroll') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.payroll') ? 'active' : '' }}"><i class="fas fa-wallet ict-nav-icon"></i> {{ __('messages.payroll_report') }}</a></li>
                        @endif

                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.sales') }}</li>
                        @if(Route::has('admin.reports.sales'))
                            <li><a href="{{ route('admin.reports.sales') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar ict-nav-icon"></i> {{ __('messages.sales') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.customers'))
                            <li><a href="{{ route('admin.reports.customers') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }}"><i class="fas fa-users ict-nav-icon"></i> {{ __('messages.customers') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.quotations'))
                            <li><a href="{{ route('admin.reports.quotations') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.quotations') ? 'active' : '' }}"><i class="fas fa-file-invoice ict-nav-icon"></i> {{ __('messages.quotations') }}</a></li>
                        @endif

                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.purchase') }}</li>
                        @if(Route::has('admin.reports.purchase-orders'))
                            <li><a href="{{ route('admin.reports.purchase-orders') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.purchase-orders') ? 'active' : '' }}"><i class="fas fa-shopping-cart ict-nav-icon"></i> {{ __('messages.purchase_orders') }}</a></li>
                        @endif
                        @if(Route::has('admin.reports.suppliers'))
                            <li><a href="{{ route('admin.reports.suppliers') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.suppliers') ? 'active' : '' }}"><i class="fas fa-truck ict-nav-icon"></i> {{ __('messages.suppliers') }}</a></li>
                        @endif

                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.users') }}</li>
                        @if(Route::has('admin.reports.users'))
                            <li><a href="{{ route('admin.reports.users') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.users') ? 'active' : '' }}"><i class="fas fa-users-cog ict-nav-icon"></i> {{ __('messages.users') }}</a></li>
                        @endif

                        <li class="ict-nav-category" style="font-size:0.6rem;padding:8px 12px 4px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">{{ __('messages.financial') }}</li>
                        @if(Route::has('admin.reports.profit-loss'))
                            <li><a href="{{ route('admin.reports.profit-loss') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.profit-loss') ? 'active' : '' }}"><i class="fas fa-chart-line ict-nav-icon"></i> {{ __('messages.profit_loss') }}</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Notifications --}}
            @if($canViewAll || auth()->user()->can('notification.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.notifications.index') }}" class="ict-nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <i class="fas fa-bell ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.notifications') }}</span>
                        <span class="ict-nav-badge" id="notification-count">0</span>
                    </a>
                </li>
            @endif

            <!-- SYSTEM -->
            <li class="ict-nav-category">{{ __('messages.system') }}</li>

            {{-- Users Management --}}
            @if($canViewAll || auth()->user()->can('user.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.users.index') }}" class="ict-nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <i class="fas fa-user-cog ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.users_management') }}</span>
                    </a>
                </li>
            @endif

            {{-- Roles --}}
            @if($canViewAll || auth()->user()->can('role.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.roles.index') }}" class="ict-nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <i class="fas fa-user-shield ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.roles') }}</span>
                    </a>
                </li>
            @endif

            {{-- Permissions --}}
            @if($canViewAll || auth()->user()->can('role.view'))
                <li class="ict-nav-item">
                    <a href="{{ route('admin.permissions.index') }}" class="ict-nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                        <i class="fas fa-key ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.permissions') }}</span>
                    </a>
                </li>
            @endif

            <!-- CONFIGURATION -->
            <li class="ict-nav-category">{{ __('messages.configuration') }}</li>

            {{-- Settings --}}
            @if($canViewAll || auth()->user()->can('settings.view'))
                <li class="ict-nav-item ict-has-dropdown {{ request()->routeIs('admin.settings.*') ? 'open' : '' }}">
                    <div class="ict-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" data-target="settings-dropdown">
                        <i class="fas fa-cog ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.settings') }}</span>
                        <i class="fas fa-chevron-right ict-dropdown-arrow"></i>
                    </div>
                    <ul class="ict-nav-submenu {{ request()->routeIs('admin.settings.*') ? 'show' : '' }}" id="settings-dropdown">
                        @if(Route::has('admin.settings.general'))
                            <li><a href="{{ route('admin.settings.general') }}" class="ict-nav-link"><i class="fas fa-sliders-h ict-nav-icon"></i> {{ __('messages.general') }}</a></li>
                        @endif
                        @if(Route::has('admin.settings.email'))
                            <li><a href="{{ route('admin.settings.email') }}" class="ict-nav-link"><i class="fas fa-envelope ict-nav-icon"></i> {{ __('messages.email') }}</a></li>
                        @endif
                        @if(Route::has('admin.settings.security'))
                            <li><a href="{{ route('admin.settings.security') }}" class="ict-nav-link"><i class="fas fa-shield-alt ict-nav-icon"></i> {{ __('messages.security') }}</a></li>
                        @endif
                        @if(Route::has('admin.settings.backup'))
                            <li><a href="{{ route('admin.settings.backup') }}" class="ict-nav-link"><i class="fas fa-database ict-nav-icon"></i> {{ __('messages.backup') }}</a></li>
                        @endif
                        @if(Route::has('admin.settings.logs'))
                            <li><a href="{{ route('admin.settings.logs') }}" class="ict-nav-link"><i class="fas fa-file-alt ict-nav-icon"></i> {{ __('messages.logs') }}</a></li>
                        @endif
                        @if(Route::has('admin.settings.system-info'))
                            <li><a href="{{ route('admin.settings.system-info') }}" class="ict-nav-link"><i class="fas fa-info-circle ict-nav-icon"></i> {{ __('messages.system_info') }}</a></li>
                        @endif
                        @if(Route::has('admin.currencies.index'))
                            <li><a href="{{ route('admin.currencies.index') }}" class="ict-nav-link"><i class="fas fa-money-bill-wave ict-nav-icon"></i> {{ __('messages.currencies') }}</a></li>
                        @endif
                        @if(Route::has('admin.tax-rates.index'))
                            <li><a href="{{ route('admin.tax-rates.index') }}" class="ict-nav-link"><i class="fas fa-percent ict-nav-icon"></i> {{ __('messages.tax_rates') }}</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- ACCOUNT -->
            <li class="ict-nav-category">{{ __('messages.account') }}</li>

            {{-- Profile --}}
            <li class="ict-nav-item">
                <a href="{{ route('profile.edit') }}" class="ict-nav-link">
                    <i class="fas fa-user ict-nav-icon"></i>
                    <span class="ict-nav-text">{{ __('messages.profile') }}</span>
                </a>
            </li>

            {{-- Logout --}}
            <li class="ict-nav-item">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" class="ict-nav-link text-danger">
                        <i class="fas fa-sign-out-alt ict-nav-icon"></i>
                        <span class="ict-nav-text">{{ __('messages.logout') }}</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="ict-sidebar-footer">
        <div class="ict-footer-links">
            <a href="#" class="ict-footer-link" id="ictCollapseSidebar">
                <i class="fas fa-chevron-left"></i> <span>{{ __('messages.collapse') }}</span>
            </a>
            <a href="#" class="ict-footer-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> <span>{{ __('messages.logout') }}</span>
            </a>
            <a href="#" class="ict-footer-link" onclick="window.location.reload();">
                <i class="fas fa-sync-alt"></i> <span>{{ __('messages.refresh') }}</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggles
    var dropdownToggles = document.querySelectorAll('.ict-has-dropdown > .ict-nav-link');
    
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var parent = this.closest('.ict-has-dropdown');
            var submenu = parent.querySelector('.ict-nav-submenu');
            
            if (submenu) {
                var openDropdowns = document.querySelectorAll('.ict-has-dropdown.open');
                openDropdowns.forEach(function(open) {
                    if (open !== parent) {
                        open.classList.remove('open');
                        var openSubmenu = open.querySelector('.ict-nav-submenu');
                        if (openSubmenu) {
                            openSubmenu.classList.remove('show');
                        }
                    }
                });
                
                parent.classList.toggle('open');
                submenu.classList.toggle('show');
            }
        });
    });

    // Mobile toggle
    var sidebar = document.getElementById('ictSidebar');
    var toggleBtn = document.getElementById('ictSidebarToggle');
    var overlay = document.getElementById('ictSidebarOverlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-mobile-open');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            document.body.classList.remove('sidebar-mobile-open');
            overlay.classList.remove('active');
        });
    }

    // Collapse/Expand
    var collapseBtn = document.getElementById('ictCollapseSidebar');
    var isCollapsed = localStorage.getItem('ictSidebarCollapsed') === 'true';

    if (collapseBtn) {
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
            collapseBtn.querySelector('i').classList.replace('fa-chevron-left', 'fa-chevron-right');
            collapseBtn.querySelector('span').textContent = '{{ __('messages.expand') }}';
        }

        collapseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-collapsed');
            var nowCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('ictSidebarCollapsed', nowCollapsed);
            if (nowCollapsed) {
                collapseBtn.querySelector('i').classList.replace('fa-chevron-left', 'fa-chevron-right');
                collapseBtn.querySelector('span').textContent = '{{ __('messages.expand') }}';
            } else {
                collapseBtn.querySelector('i').classList.replace('fa-chevron-right', 'fa-chevron-left');
                collapseBtn.querySelector('span').textContent = '{{ __('messages.collapse') }}';
            }
        });
    }

    // Fetch notification count
    function updateBadge(elementId, url, property, fallback) {
        property = property || 'count';
        fallback = fallback || 0;
        var element = document.getElementById(elementId);
        if (!element) return;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            },
            credentials: 'same-origin'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var count = data[property] || data.count || fallback;
            element.textContent = count;
            element.style.display = count > 0 ? 'inline-block' : 'none';
        })
        .catch(function() {
            element.style.display = 'none';
        });
    }

    function fetchAllCounts() {
        var notifUrl = '{{ route("api.notifications.count") }}';
        if (notifUrl && !notifUrl.includes('route')) {
            updateBadge('notification-count', notifUrl);
        }
    }

    fetchAllCounts();
    setInterval(fetchAllCounts, 30000);

    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            fetchAllCounts();
        }
    });
});
</script>
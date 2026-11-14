<style>
/* ============================================
   ICT SIDEBAR - Refined (unchanged)
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
    transition: width 0.3s ease, transform 0.3s ease;
    overflow: hidden;
    box-shadow: 4px 0 30px rgba(0,0,0,0.3);
}

.ict-sidebar-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 12px 10px 20px;
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
    padding: 18px 20px;
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
    font-size: 1.2rem;
    color: #fff;
    flex-shrink: 0;
}

.ict-logo-text h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
}
.ict-logo-text span {
    font-size: 0.6rem;
    color: var(--sidebar-text-muted);
    display: block;
    letter-spacing: 0.5px;
}

/* User Profile */
.ict-user-profile {
    padding: 16px 16px 20px;
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    gap: 12px;
}

.ict-user-avatar {
    width: 44px;
    height: 44px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
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
    background: #22c55e;
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
    margin: 2px 0 0;
    font-size: 0.7rem;
    color: var(--sidebar-text-muted);
}

.ict-role-badge {
    display: inline-block;
    background: rgba(79,70,229,0.15);
    color: #818cf8;
    font-size: 0.6rem;
    padding: 2px 10px;
    border-radius: 20px;
    margin-top: 4px;
    font-weight: 500;
}

/* Navigation */
.ict-nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
}

.ict-nav-category {
    padding: 20px 12px 6px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--sidebar-text-muted);
}

.ict-nav-item {
    margin-bottom: 1px;
}

.ict-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 12px;
    border-radius: 8px;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: all 0.15s ease;
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
.ict-nav-text {
    flex: 1;
}

/* Dropdown */
.ict-has-dropdown {
    position: relative;
}
.ict-has-dropdown > .ict-nav-link {
    padding-right: 35px;
}
.ict-dropdown-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.7rem;
    transition: transform 0.25s ease;
}
.ict-has-dropdown.open > .ict-nav-link .ict-dropdown-arrow {
    transform: translateY(-50%) rotate(90deg);
}

.ict-nav-submenu {
    list-style: none;
    margin: 0;
    padding: 0 0 0 34px;
    display: none;
}
.ict-nav-submenu.show {
    display: block;
}
.ict-nav-submenu .ict-nav-link {
    padding: 6px 12px;
    font-size: 0.8rem;
}
.ict-nav-submenu .ict-nav-icon {
    width: 18px;
    font-size: 0.8rem;
}

/* Badge */
.ict-nav-badge {
    background: #ef4444;
    color: #fff;
    font-size: 0.6rem;
    padding: 2px 7px;
    border-radius: 20px;
    margin-left: auto;
    font-weight: 600;
}
.ict-nav-badge-success {
    background: #22c55e;
}

/* Footer */
.ict-sidebar-footer {
    padding: 14px 20px;
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
    box-shadow: 0 2px 10px rgba(0,0,0,0.25);
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
body.sidebar-collapsed .ict-sidebar {
    width: var(--sidebar-collapsed-width);
}

/* Mobile */
@media (max-width: 768px) {
    .ict-sidebar {
        transform: translateX(-100%);
        box-shadow: none;
        width: var(--sidebar-width);
    }
    body.sidebar-mobile-open .ict-sidebar {
        transform: translateX(0);
        box-shadow: 4px 0 30px rgba(0,0,0,0.3);
    }
    .ict-sidebar-toggle {
        display: flex;
    }
    .ict-sidebar-overlay.active {
        display: block;
    }
    body.sidebar-collapsed .ict-sidebar {
        width: var(--sidebar-width);
    }
    body.sidebar-collapsed .ict-nav-text,
    body.sidebar-collapsed .ict-dropdown-arrow,
    body.sidebar-collapsed .ict-nav-badge {
        display: inline;
    }
    body.sidebar-collapsed .ict-nav-link {
        justify-content: flex-start;
        padding: 9px 12px;
    }
    body.sidebar-collapsed .ict-nav-icon {
        font-size: 1.1rem;
    }
    body.sidebar-collapsed .ict-user-info,
    body.sidebar-collapsed .ict-logo-text {
        display: block;
    }
    body.sidebar-collapsed .ict-user-profile {
        justify-content: flex-start;
    }
    body.sidebar-collapsed .ict-logo-icon {
        margin: 0;
    }
}
</style>

@php
    $user = auth()->user();
    $displayRole = $user->role ?? 'User';
@endphp

<!-- Toggle Button -->
<button class="ict-sidebar-toggle" id="ictSidebarToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Mobile Overlay -->
<div class="ict-sidebar-overlay" id="ictSidebarOverlay"></div>

<!-- Sidebar -->
<div class="ict-sidebar" id="ictSidebar">
    <!-- Logo -->
    <div class="ict-sidebar-logo">
        <div class="ict-logo-icon"><i class="fas fa-server"></i></div>
        <div class="ict-logo-text">
            <h2>ICT Admin</h2>
            <span>Inventory Management</span>
        </div>
    </div>

    <!-- User Profile -->
    <div class="ict-user-profile">
        <div class="ict-user-avatar"><i class="fas fa-user"></i></div>
        <div class="ict-user-info">
            <h5>{{ $user->name ?? 'Admin' }}</h5>
            <div class="ict-role-badge"><i class="fas fa-tag"></i> {{ ucfirst($displayRole) }}</div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="ict-sidebar-scroll">
        <ul class="ict-nav-menu">
            <li class="ict-nav-category">MAIN</li>

            {{-- Dashboard --}}
            @can('dashboard.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="ict-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 ict-nav-icon"></i>
                        <span class="ict-nav-text">Dashboard</span>
                    </a>
                </li>
            @endcan

            {{-- Asset Requests --}}
            @can('asset_request.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.asset-requests.index') }}" class="ict-nav-link {{ request()->routeIs('admin.asset-requests.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-list ict-nav-icon"></i>
                        <span class="ict-nav-text">Asset Requests</span>
                        <span class="ict-nav-badge" id="pending-request-count">0</span>
                    </a>
                </li>
            @endcan

            <li class="ict-nav-category">MANAGEMENT</li>

            {{-- Products with submenu --}}
            @can('product.view')
                <li class="ict-nav-item ict-has-dropdown {{ request()->routeIs('admin.products.*') ? 'open' : '' }}">
                    <div class="ict-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="bi bi-box ict-nav-icon"></i>
                        <span class="ict-nav-text">Products</span>
                        <i class="bi bi-chevron-right ict-dropdown-arrow"></i>
                    </div>
                    <ul class="ict-nav-submenu {{ request()->routeIs('admin.products.*') ? 'show' : '' }}">
                        <li><a href="{{ route('admin.products.index') }}" class="ict-nav-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">All Products</a></li>
                        @can('product.create')
                            <li><a href="{{ route('admin.products.create') }}" class="ict-nav-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">Add Product</a></li>
                        @endcan
                        @can('stock.view')
                            <li><a href="{{ route('admin.products.low-stock') }}" class="ict-nav-link {{ request()->routeIs('admin.products.low-stock') ? 'active' : '' }}">Low Stock</a></li>
                        @endcan
                    </ul>
                </li>
            @endcan

            {{-- Categories --}}
            @can('category.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="ict-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags ict-nav-icon"></i>
                        <span class="ict-nav-text">Categories</span>
                    </a>
                </li>
            @endcan

            {{-- Suppliers --}}
            @can('supplier.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.suppliers.index') }}" class="ict-nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                        <i class="bi bi-truck ict-nav-icon"></i>
                        <span class="ict-nav-text">Suppliers</span>
                    </a>
                </li>
            @endcan

            {{-- Departments --}}
            @can('department.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.departments.index') }}" class="ict-nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                        <i class="bi bi-building ict-nav-icon"></i>
                        <span class="ict-nav-text">Departments</span>
                    </a>
                </li>
            @endcan

            {{-- Assignments --}}
            @can('assignment.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.assignments.index') }}" class="ict-nav-link {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">
                        <i class="bi bi-hand-over ict-nav-icon"></i>
                        <span class="ict-nav-text">Assignments</span>
                        <span class="ict-nav-badge ict-nav-badge-success" id="active-assignment-count">0</span>
                    </a>
                </li>
            @endcan

            {{-- Purchase Orders --}}
            @can('purchase_order.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="ict-nav-link {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                        <i class="bi bi-cart ict-nav-icon"></i>
                        <span class="ict-nav-text">Purchase Orders</span>
                    </a>
                </li>
            @endcan

            {{-- Maintenance --}}
            @can('maintenance.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.maintenance.index') }}" class="ict-nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                        <i class="bi bi-tools ict-nav-icon"></i>
                        <span class="ict-nav-text">Maintenance</span>
                    </a>
                </li>
            @endcan

            <li class="ict-nav-category">SYSTEM</li>

            {{-- Users --}}
            @can('user.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.users.index') }}" class="ict-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people ict-nav-icon"></i>
                        <span class="ict-nav-text">Users</span>
                    </a>
                </li>
            @endcan

            {{-- Roles --}}
            @can('role.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.roles.index') }}" class="ict-nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock ict-nav-icon"></i>
                        <span class="ict-nav-text">Roles</span>
                    </a>
                </li>
            @endcan

            {{-- Reports --}}
            @can('report.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.reports.index') }}" class="ict-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart ict-nav-icon"></i>
                        <span class="ict-nav-text">Reports</span>
                    </a>
                </li>
            @endcan

            {{-- Settings --}}
            @can('settings.view')
                <li class="ict-nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="ict-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear ict-nav-icon"></i>
                        <span class="ict-nav-text">Settings</span>
                    </a>
                </li>
            @endcan

            <li class="ict-nav-category">ACCOUNT</li>

            {{-- Profile --}}
            <li class="ict-nav-item">
                <a href="{{ route('profile.edit') }}" class="ict-nav-link">
                    <i class="bi bi-person ict-nav-icon"></i>
                    <span class="ict-nav-text">My Profile</span>
                </a>
            </li>

            {{-- Logout --}}
            <li class="ict-nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ict-nav-link text-danger">
                        <i class="bi bi-box-arrow-right ict-nav-icon"></i>
                        <span class="ict-nav-text">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="ict-sidebar-footer">
        <div class="ict-footer-links">
            <a href="#" class="ict-footer-link" id="ictCollapseSidebar">
                <i class="bi bi-chevron-left"></i> <span>Collapse</span>
            </a>
            <a href="#" class="ict-footer-link">
                <i class="bi bi-question-circle"></i> <span>Help</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggles
    document.querySelectorAll('.ict-has-dropdown > .ict-nav-link').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const parent = this.closest('.ict-has-dropdown');
            const submenu = parent.querySelector('.ict-nav-submenu');
            if (submenu) {
                document.querySelectorAll('.ict-has-dropdown.open').forEach(s => {
                    if (s !== parent) { s.classList.remove('open'); s.querySelector('.ict-nav-submenu')?.classList.remove('show'); }
                });
                parent.classList.toggle('open');
                submenu.classList.toggle('show');
            }
        });
    });

    // Mobile toggle
    const sidebar = document.getElementById('ictSidebar');
    const toggleBtn = document.getElementById('ictSidebarToggle');
    const overlay = document.getElementById('ictSidebarOverlay');
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
    const collapseBtn = document.getElementById('ictCollapseSidebar');
    let isCollapsed = localStorage.getItem('ictSidebarCollapsed') === 'true';
    if (collapseBtn) {
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
            collapseBtn.querySelector('i').classList.replace('bi-chevron-left', 'bi-chevron-right');
            collapseBtn.querySelector('span').textContent = 'Expand';
        }
        collapseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-collapsed');
            const nowCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('ictSidebarCollapsed', nowCollapsed);
            if (nowCollapsed) {
                collapseBtn.querySelector('i').classList.replace('bi-chevron-left', 'bi-chevron-right');
                collapseBtn.querySelector('span').textContent = 'Expand';
            } else {
                collapseBtn.querySelector('i').classList.replace('bi-chevron-right', 'bi-chevron-left');
                collapseBtn.querySelector('span').textContent = 'Collapse';
            }
        });
    }
});
</script>
<!-- User Sidebar -->
<style>
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
    --primary-hover: #4338ca;
    --danger-color: #ef4444;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
}

.uct-sidebar {
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

.uct-sidebar-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px 12px;
}

.uct-sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.uct-sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.uct-sidebar-scroll::-webkit-scrollbar-thumb {
    background: var(--sidebar-hover);
    border-radius: 4px;
}

/* Logo */
.uct-sidebar-logo {
    padding: 20px;
    background: var(--sidebar-dark);
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    gap: 12px;
}

.uct-logo-icon {
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

.uct-logo-text h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
}
.uct-logo-text span {
    font-size: 0.65rem;
    color: var(--sidebar-text-muted);
    display: block;
}

/* User Profile */
.uct-user-profile {
    padding: 20px 16px;
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    gap: 12px;
}

.uct-user-avatar {
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

.uct-user-avatar::after {
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

.uct-user-info h5 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
}
.uct-user-info p {
    margin: 4px 0 0;
    font-size: 0.7rem;
    color: var(--sidebar-text-muted);
}

.uct-role-badge {
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
.uct-nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
}

.uct-nav-category {
    padding: 16px 12px 8px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--sidebar-text-muted);
    border-bottom: 1px solid var(--sidebar-border);
    margin-bottom: 8px;
}

.uct-nav-item {
    margin-bottom: 2px;
}

.uct-nav-link {
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

.uct-nav-link:hover {
    background: var(--sidebar-hover);
    color: #fff;
}
.uct-nav-link.active {
    background: rgba(79,70,229,0.15);
    color: #818cf8;
}
.uct-nav-link.active .uct-nav-icon {
    color: #818cf8;
}

.uct-nav-icon {
    width: 22px;
    font-size: 1.1rem;
    text-align: center;
    flex-shrink: 0;
}
.uct-nav-text {
    flex: 1;
}

/* Badges */
.uct-nav-badge {
    background: var(--danger-color);
    color: #fff;
    font-size: 0.6rem;
    padding: 2px 8px;
    border-radius: 20px;
    margin-left: auto;
    font-weight: 600;
    min-width: 20px;
    text-align: center;
    transition: all 0.3s ease;
}
.uct-nav-badge-success {
    background: var(--success-color);
}
.uct-nav-badge-warning {
    background: var(--warning-color);
    color: #1e293b;
}
.uct-nav-badge-info {
    background: #3b82f6;
}
.uct-nav-badge-secondary {
    background: #6b7280;
}

/* Badge animation */
.uct-nav-badge.badge-updated {
    animation: badgePulse 0.5s ease;
}

@keyframes badgePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

/* Footer */
.uct-sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--sidebar-border);
    background: var(--sidebar-dark);
}
.uct-footer-links {
    display: flex;
    justify-content: space-around;
}
.uct-footer-link {
    color: var(--sidebar-text-muted);
    text-decoration: none;
    font-size: 0.7rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 5px;
}
.uct-footer-link:hover {
    color: #818cf8;
}

/* Toggle Button */
.uct-sidebar-toggle {
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
.uct-sidebar-toggle:hover {
    background: var(--primary-hover);
    transform: scale(1.05);
}

/* Mobile Overlay */
.uct-sidebar-overlay {
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
body.sidebar-collapsed .uct-nav-text,
body.sidebar-collapsed .uct-nav-badge {
    display: none;
}
body.sidebar-collapsed .uct-nav-link {
    justify-content: center;
    padding: 12px;
}
body.sidebar-collapsed .uct-nav-icon {
    margin-right: 0;
    font-size: 1.25rem;
}
body.sidebar-collapsed .uct-user-info,
body.sidebar-collapsed .uct-logo-text {
    display: none;
}
body.sidebar-collapsed .uct-user-avatar {
    margin: 0 auto;
}
body.sidebar-collapsed .uct-logo-icon {
    margin: 0 auto;
}
body.sidebar-collapsed .uct-user-profile {
    justify-content: center;
}
body.sidebar-collapsed .uct-footer-links span {
    display: none;
}

/* Mobile */
@media (max-width: 768px) {
    .uct-sidebar {
        transform: translateX(-100%);
        box-shadow: none;
    }
    body.sidebar-mobile-open .uct-sidebar {
        transform: translateX(0);
        box-shadow: 4px 0 20px rgba(0,0,0,0.2);
    }
    .uct-sidebar-toggle {
        display: flex;
    }
    .uct-sidebar-overlay.active {
        display: block;
    }
}
</style>

@php
    $user = auth()->user();
    $displayRole = $user->roles->first()->name ?? 'User';
    $activeAssignments = \App\Models\Assignment::where('user_id', $user->id)
        ->whereNull('actual_return_date')
        ->count();
    $overdueAssignments = \App\Models\Assignment::where('user_id', $user->id)
        ->whereNull('actual_return_date')
        ->where('expected_return_date', '<', now())
        ->count();
    $pendingRequests = \App\Models\AssetRequest::where('user_id', $user->id)
        ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
        ->count();
    $unreadCount = \App\Models\Notification::where('user_id', $user->id)
        ->where('is_read', false)
        ->count();
@endphp

<!-- Toggle Button -->
<button class="uct-sidebar-toggle" id="uctSidebarToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Mobile Overlay -->
<div class="uct-sidebar-overlay" id="uctSidebarOverlay"></div>

<!-- Sidebar -->
<div class="uct-sidebar" id="uctSidebar">
    <!-- Logo -->
    <div class="uct-sidebar-logo">
        <div class="uct-logo-icon"><i class="fas fa-laptop"></i></div>
        <div class="uct-logo-text">
            <h2>ICT User</h2>
            <span>Inventory Portal</span>
        </div>
    </div>

    <!-- User Profile -->
    <div class="uct-user-profile">
        <div class="uct-user-avatar"><i class="fas fa-user"></i></div>
        <div class="uct-user-info">
            <h5>{{ $user->name ?? 'User' }}</h5>
            <div class="uct-role-badge"><i class="fas fa-tag"></i> {{ ucfirst($displayRole) }}</div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="uct-sidebar-scroll">
        <ul class="uct-nav-menu">
            <li class="uct-nav-category">MAIN</li>
            
            <!-- Dashboard -->
            <li class="uct-nav-item">
                <a href="{{ route('user.dashboard') }}" class="uct-nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 uct-nav-icon"></i>
                    <span class="uct-nav-text">Dashboard</span>
                </a>
            </li>

            <li class="uct-nav-category">REQUESTS</li>

            <!-- My Requests -->
            <li class="uct-nav-item">
                <a href="{{ route('user.asset-requests.index') }}" class="uct-nav-link {{ request()->routeIs('user.asset-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-list uct-nav-icon"></i>
                    <span class="uct-nav-text">My Requests</span>
                    <span class="uct-nav-badge uct-nav-badge-info" id="pending-request-count">{{ $pendingRequests }}</span>
                </a>
            </li>

            <!-- New Request -->
            <li class="uct-nav-item">
                <button class="uct-nav-link" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                    <i class="bi bi-plus-circle uct-nav-icon"></i>
                    <span class="uct-nav-text">New Request</span>
                </button>
            </li>

            <li class="uct-nav-category">ASSETS</li>

            <!-- My Assignments -->
            <li class="uct-nav-item">
                <a href="{{ route('user.assignments.index') }}" class="uct-nav-link {{ request()->routeIs('user.assignments.*') ? 'active' : '' }}">
                    <i class="bi bi-laptop uct-nav-icon"></i>
                    <span class="uct-nav-text">My Assignments</span>
                    <span class="uct-nav-badge uct-nav-badge-success" id="active-assignment-count">{{ $activeAssignments }}</span>
                </a>
            </li>

            <!-- Assignment History -->
            <li class="uct-nav-item">
                <a href="{{ route('user.assignments.history') }}" class="uct-nav-link {{ request()->routeIs('user.assignments.history') ? 'active' : '' }}">
                    <i class="bi bi-clock-history uct-nav-icon"></i>
                    <span class="uct-nav-text">History</span>
                </a>
            </li>

            <!-- Available Products - FIXED ROUTE -->
            <li class="uct-nav-item">
                <a href="{{ route('user.products') }}" class="uct-nav-link {{ request()->routeIs('user.products*') ? 'active' : '' }}">
                    <i class="bi bi-box uct-nav-icon"></i>
                    <span class="uct-nav-text">Available Products</span>
                </a>
            </li>

            <li class="uct-nav-category">ACCOUNT</li>

            <!-- My Profile -->
            <li class="uct-nav-item">
                <a href="{{ route('profile.edit') }}" class="uct-nav-link">
                    <i class="bi bi-person uct-nav-icon"></i>
                    <span class="uct-nav-text">My Profile</span>
                </a>
            </li>

            <!-- Notifications -->
            <li class="uct-nav-item">
                <a href="{{ route('user.notifications.index') }}" class="uct-nav-link {{ request()->routeIs('user.notifications.index') ? 'active' : '' }}">
                    <i class="bi bi-bell uct-nav-icon"></i>
                    <span class="uct-nav-text">Notifications</span>
                    <span class="uct-nav-badge" id="notification-count">{{ $unreadCount }}</span>
                </a>
            </li>

            <!-- Logout -->
            <li class="uct-nav-item">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" class="uct-nav-link text-danger">
                        <i class="bi bi-box-arrow-right uct-nav-icon"></i>
                        <span class="uct-nav-text">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="uct-sidebar-footer">
        <div class="uct-footer-links">
            <a href="#" class="uct-footer-link" id="uctCollapseSidebar">
                <i class="bi bi-chevron-left"></i> <span>Collapse</span>
            </a>
            <a href="#" class="uct-footer-link" onclick="window.location.reload();">
                <i class="bi bi-arrow-repeat"></i> <span>Refresh</span>
            </a>
            <a href="{{ route('user.dashboard') }}" class="uct-footer-link">
                <i class="bi bi-house"></i> <span>Home</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // SIDEBAR TOGGLE (Mobile)
    // ============================================
    const sidebar = document.getElementById('uctSidebar');
    const toggleBtn = document.getElementById('uctSidebarToggle');
    const overlay = document.getElementById('uctSidebarOverlay');
    
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

    // ============================================
    // COLLAPSE/EXPAND
    // ============================================
    const collapseBtn = document.getElementById('uctCollapseSidebar');
    let isCollapsed = localStorage.getItem('uctSidebarCollapsed') === 'true';
    
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
            localStorage.setItem('uctSidebarCollapsed', nowCollapsed);
            if (nowCollapsed) {
                collapseBtn.querySelector('i').classList.replace('bi-chevron-left', 'bi-chevron-right');
                collapseBtn.querySelector('span').textContent = 'Expand';
            } else {
                collapseBtn.querySelector('i').classList.replace('bi-chevron-right', 'bi-chevron-left');
                collapseBtn.querySelector('span').textContent = 'Collapse';
            }
        });
    }

    // ============================================
    // UPDATE BADGE COUNTS (Real-time)
    // ============================================
    function updateBadge(elementId, url, property) {
        const element = document.getElementById(elementId);
        if (!element) return;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            const count = property ? data[property] : (data.count || data.total || 0);
            if (count !== undefined && count !== null) {
                const oldText = element.textContent;
                element.textContent = count;
                
                if (oldText !== count.toString()) {
                    element.classList.add('badge-updated');
                    setTimeout(() => {
                        element.classList.remove('badge-updated');
                    }, 500);
                }
                
                if (count == 0) {
                    element.style.display = 'none';
                } else {
                    element.style.display = 'inline-block';
                }
            }
        })
        .catch(error => {
            console.warn('Failed to update badge:', elementId, error);
        });
    }

    // Update all badges
    function updateAllBadges() {
        // Active Assignments
        const activeEl = document.getElementById('active-assignment-count');
        if (activeEl) {
            updateBadge('active-assignment-count', '{{ route("user.assignments.widget") }}', 'active_assignments');
        }

        // Pending Requests
        const pendingEl = document.getElementById('pending-request-count');
        if (pendingEl) {
            updateBadge('pending-request-count', '{{ route("api.user.requests.count") }}', 'count');
        }

        // Notifications
        const notifEl = document.getElementById('notification-count');
        if (notifEl) {
            updateBadge('notification-count', '{{ route("api.notifications.count") }}', 'count');
        }
    }

    // Initial update after page load
    setTimeout(updateAllBadges, 500);

    // Update every 60 seconds
    setInterval(updateAllBadges, 60000);

    // Update when page becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateAllBadges();
        }
    });

    // ============================================
    // LOGOUT CONFIRMATION
    // ============================================
    document.getElementById('logout-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Logout?',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    console.log('User sidebar loaded successfully!');
});
</script>
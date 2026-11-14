<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <button class="btn btn-link d-lg-none p-0 me-3" id="mobileSidebarToggle">
                <i class="fas fa-bars fs-4"></i>
            </button>
            <h5 class="mb-0 fw-bold">@yield('page-title', 'Dashboard')</h5>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-link text-dark position-relative p-0" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fs-5"></i>
                    <!-- FIX: badge is hidden by default (style="display:none") instead of
                         always showing "0" before the first AJAX count comes back -->
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          id="notificationBadge" style="display:none;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width:320px;max-height:400px;overflow-y:auto;">
                    <li class="dropdown-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <strong><i class="fas fa-bell me-2"></i>Notifications</strong>
                        <button class="btn btn-sm btn-link text-primary p-0" id="markAllRead">Mark all read</button>
                    </li>
                    <div id="notificationList">
                        <li class="text-center py-3 text-muted"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No notifications</li>
                    </div>
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-weight:600;font-size:0.9rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2 text-primary"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="fas fa-cog me-2 text-warning"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    document.getElementById('mobileSidebarToggle')?.addEventListener('click', function() {
        document.body.classList.toggle('sidebar-mobile-open');
        document.getElementById('ictSidebarOverlay')?.classList.toggle('active');
    });

    // Mark all read
    document.getElementById('markAllRead')?.addEventListener('click', function(e) {
        e.preventDefault();
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(() => {
            document.getElementById('notificationBadge').style.display = 'none';
            document.getElementById('notificationList').innerHTML =
                '<li class="text-center py-3 text-success"><i class="fas fa-check-circle fa-2x d-block mb-2"></i>All notifications read</li>';
        });
    });

    // Update notification count
    function updateNotificationCount() {
        fetch('/api/notifications/count').then(r => r.json()).then(data => {
            const badge = document.getElementById('notificationBadge');
            if (data.count > 0) { badge.textContent = data.count; badge.style.display = ''; }
            else { badge.style.display = 'none'; }
        }).catch(() => {});
    }
    updateNotificationCount();
    setInterval(updateNotificationCount, 30000);
});
</script>
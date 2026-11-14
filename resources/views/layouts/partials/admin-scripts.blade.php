<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Toast function
function showToast(type, message) {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle', warning: 'exclamation-triangle' };
    const colors = { success: 'success', error: 'danger', info: 'info', warning: 'warning' };
    const toast = $(`
        <div class="toast show" role="alert" style="min-width:300px;position:fixed;top:20px;right:20px;z-index:9999;">
            <div class="toast-header bg-${colors[type]} text-white">
                <strong class="me-auto"><i class="fas fa-${icons[type]} me-2"></i>${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `);
    $('body').append(toast);
    setTimeout(() => toast.remove(), 5000);
}

/*
 * FIX: the previous version of updateCounts() polled
 *   #pending-request-count  and  #active-assignment-count
 * every 30 seconds, but neither id exists anywhere in the
 * admin-sidebar / admin-header markup (the sidebar instead
 * hardcodes a static badge value for Asset Requests). That
 * made this function a permanent no-op that just spent a
 * network request every 30s for nothing.
 *
 * Below, the sidebar's Asset Requests badge is now given a
 * real id (id="asset-request-badge") so this poll actually
 * updates something. Update the badge's id in
 * admin-sidebar.blade.php to match (already done in the
 * fixed version of that file).
 */
function updateCounts() {
    fetch('/api/admin/pending-requests/count').then(r => r.json()).then(data => {
        const el = document.getElementById('asset-request-badge');
        if (el) {
            if (data.count > 0) { el.textContent = data.count; el.style.display = ''; }
            else { el.style.display = 'none'; }
        }
    }).catch(() => {});
}

document.addEventListener('DOMContentLoaded', function() {
    updateCounts();
    setInterval(updateCounts, 30000);
});

// Preloader
window.addEventListener('load', function() {
    const preloader = document.getElementById('preloader');
    if (preloader) { preloader.style.opacity = '0'; setTimeout(() => preloader.style.display = 'none', 500); }
});
</script>
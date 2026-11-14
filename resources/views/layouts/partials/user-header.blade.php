<!-- User Dropdown -->
<div class="dropdown">
    <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
        <img src="{{ auth()->user()->avatar_url }}" 
             alt="{{ auth()->user()->name }}" 
             class="rounded-circle" 
             style="width: 32px; height: 32px; object-fit: cover;">
        <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
        <i class="fas fa-chevron-down small text-muted"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
            <i class="fas fa-user me-2 text-primary"></i> My Profile
        </a></li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </button>
            </form>
        </li>
    </ul>
</div>
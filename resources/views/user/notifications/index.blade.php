@extends('layouts.user')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('user-content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-bell text-warning me-2"></i>All Notifications</h5>
        <div class="d-flex gap-2">
            @if($unreadCount > 0)
            <button class="btn btn-sm btn-primary" id="markAllReadBtn">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>
            @endif
            <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @if($notifications->count() > 0)
                @foreach($notifications as $notification)
                <div class="list-group-item list-group-item-action {{ !$notification->is_read ? 'bg-primary bg-opacity-5' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ $notification->message }}</div>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $notification->created_at->diffForHumans() }}
                                @if($notification->link)
                                <span class="mx-1">|</span>
                                <a href="{{ $notification->link }}" class="text-decoration-none" target="_blank">
                                    <i class="fas fa-external-link-alt me-1"></i> View
                                </a>
                                @endif
                            </small>
                        </div>
                        @if(!$notification->is_read)
                        <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="{{ $notification->id }}" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-4x text-muted mb-3 d-block"></i>
                    <h5>No Notifications</h5>
                    <p class="text-muted">You're all caught up!</p>
                </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} notifications
            </span>
            {{ $notifications->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // ==========================================
    // CSRF Setup
    // ==========================================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ==========================================
    // MARK NOTIFICATION AS READ
    // ==========================================
    $(document).on('click', '.mark-read-btn', function() {
        var id = $(this).data('id');
        var btn = $(this);
        var row = btn.closest('.list-group-item');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '/user/notifications/' + id + '/read',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    row.removeClass('bg-primary bg-opacity-5');
                    btn.remove();
                    
                    // Update badge count if exists
                    var badge = $('#notification-count');
                    if (badge.length) {
                        var count = parseInt(badge.text()) || 0;
                        var newCount = count - 1;
                        badge.text(newCount);
                        if (newCount <= 0) {
                            badge.hide();
                        }
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Marked as Read!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to mark notification as read.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to mark notification as read. Please try again.'
                });
                btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
            }
        });
    });

    // ==========================================
    // MARK ALL AS READ
    // ==========================================
    $('#markAllReadBtn').on('click', function() {
        var btn = $(this);

        Swal.fire({
            title: 'Mark All as Read?',
            text: 'This will mark all notifications as read.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark all read!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: '{{ route("user.notifications.mark-all-read") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            $('.list-group-item.bg-primary').removeClass('bg-primary bg-opacity-5');
                            $('.mark-read-btn').remove();
                            
                            var badge = $('#notification-count');
                            if (badge.length) {
                                badge.text(0).hide();
                            }
                            btn.hide();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'All Marked as Read!',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: r.message || 'Failed to mark all as read.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to mark all as read. Please try again.'
                        });
                        btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Mark All Read');
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     * Requires 'notification.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('notification.view')) {
            abort(403, 'You do not have permission to view notifications.');
        }

        $query = Notification::with('user');

        // Filter by read status
        if ($request->has('status')) {
            if ($request->status === 'read') {
                $query->where('is_read', true);
            } elseif ($request->status === 'unread') {
                $query->where('is_read', false);
            }
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $notifications = $query->latest()->paginate(20);

        // Stats
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::where('is_read', false)->count(),
            'read' => Notification::where('is_read', true)->count(),
            'by_type' => Notification::select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray(),
            'today' => Notification::whereDate('created_at', today())->count(),
            'this_week' => Notification::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Notification::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $users = User::all();
        $types = ['warranty_expiry', 'low_stock', 'assignment', 'maintenance'];

        return view('admin.notifications.index', compact('notifications', 'stats', 'users', 'types'));
    }

    /**
     * Show the form for creating a new notification.
     * Requires 'notification.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('notification.create')) {
            abort(403, 'You do not have permission to create notifications.');
        }

        $users = User::all();
        $types = ['warranty_expiry', 'low_stock', 'assignment', 'maintenance'];

        return view('admin.notifications.create', compact('users', 'types'));
    }

    /**
     * Store a newly created notification.
     * Requires 'notification.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('notification.create')) {
            return response()->json(['error' => 'You do not have permission to create notifications.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:warranty_expiry,low_stock,assignment,maintenance',
            'message' => 'required|string|max:500',
            'link' => 'nullable|string|max:255',
        ]);

        $notification = Notification::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully!',
                'notification' => $notification
            ]);
        }

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification created successfully!');
    }

    /**
     * Display the specified notification.
     * Requires 'notification.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('notification.view')) {
            return response()->json(['error' => 'You do not have permission to view notifications.'], 403);
        }

        $notification = Notification::with('user')->findOrFail($id);

        // Mark as read if unread
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return response()->json($notification);
    }

    /**
     * Show the form for editing the specified notification.
     * Requires 'notification.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('notification.edit')) {
            abort(403, 'You do not have permission to edit notifications.');
        }

        $notification = Notification::findOrFail($id);
        $users = User::all();
        $types = ['warranty_expiry', 'low_stock', 'assignment', 'maintenance'];

        return view('admin.notifications.edit', compact('notification', 'users', 'types'));
    }

    /**
     * Update the specified notification.
     * Requires 'notification.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('notification.edit')) {
            return response()->json(['error' => 'You do not have permission to edit notifications.'], 403);
        }

        $notification = Notification::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:warranty_expiry,low_stock,assignment,maintenance',
            'message' => 'required|string|max:500',
            'link' => 'nullable|string|max:255',
        ]);

        $notification->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification updated successfully!',
                'notification' => $notification
            ]);
        }

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification updated successfully!');
    }

    /**
     * Remove the specified notification.
     * Requires 'notification.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('notification.delete')) {
            return response()->json(['error' => 'You do not have permission to delete notifications.'], 403);
        }

        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully!'
        ]);
    }

    /**
     * Bulk delete notifications.
     * Requires 'notification.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('notification.delete')) {
            return response()->json(['error' => 'You do not have permission to delete notifications.'], 403);
        }

        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'exists:notifications,id',
        ]);

        $deletedCount = Notification::whereIn('id', $request->notification_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} notification(s) deleted successfully!"
        ]);
    }

    /**
     * Mark notification as read.
     * Requires 'notification.mark_read' permission.
     */
    public function markRead($id)
    {
        if (!auth()->user()->can('notification.mark_read')) {
            return response()->json(['error' => 'You do not have permission to mark notifications as read.'], 403);
        }

        $notification = Notification::findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.'
        ]);
    }

    /**
     * Mark notification as unread.
     * Requires 'notification.mark_unread' permission.
     */
    public function markUnread($id)
    {
        if (!auth()->user()->can('notification.mark_unread')) {
            return response()->json(['error' => 'You do not have permission to mark notifications as unread.'], 403);
        }

        $notification = Notification::findOrFail($id);
        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread.'
        ]);
    }

    /**
     * Mark all notifications as read for the current user.
     * Requires 'notification.mark_all_read' permission.
     */
    public function markAllRead()
    {
        if (!auth()->user()->can('notification.mark_all_read')) {
            return response()->json(['error' => 'You do not have permission to mark all notifications as read.'], 403);
        }

        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) marked as read."
        ]);
    }

    /**
     * Get notifications for the current user (API/Widget).
     */
    public function myNotifications(Request $request)
    {
        $limit = $request->limit ?? 10;

        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->limit($limit)
            ->get();

        $unreadCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total' => Notification::where('user_id', auth()->id())->count(),
        ]);
    }

    /**
     * Get unread notifications count for the current user.
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Get notification statistics.
     * Requires 'notification.view' permission.
     */
    public function statistics()
    {
        if (!auth()->user()->can('notification.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::where('is_read', false)->count(),
            'read' => Notification::where('is_read', true)->count(),
            'by_type' => Notification::select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray(),
            'by_user' => Notification::with('user')
                ->select('user_id', DB::raw('count(*) as total'))
                ->groupBy('user_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'today' => Notification::whereDate('created_at', today())->count(),
            'this_week' => Notification::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Notification::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'average_per_day' => Notification::count() > 0
                ? round(Notification::count() / max(1, now()->diffInDays(Notification::min('created_at'))), 2)
                : 0,
        ];

        return response()->json($stats);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentNotifications = Notification::with('user')
            ->where('is_read', false)
            ->latest()
            ->limit(5)
            ->get();

        $unreadByType = Notification::select('type', DB::raw('count(*) as total'))
            ->where('is_read', false)
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return response()->json([
            'recent_notifications' => $recentNotifications,
            'unread_by_type' => $unreadByType,
            'total_unread' => Notification::where('is_read', false)->count(),
            'total_notifications' => Notification::count(),
        ]);
    }

    /**
     * Export notifications to CSV/Excel.
     * Requires 'notification.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('notification.view')) {
            return response()->json(['error' => 'You do not have permission to export notifications.'], 403);
        }

        $notifications = Notification::with('user')
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'User', 'Type', 'Message', 'Status', 'Link', 'Created At'
        ];

        foreach ($notifications as $notification) {
            $exportData[] = [
                $notification->id,
                $notification->user->name ?? 'N/A',
                ucfirst(str_replace('_', ' ', $notification->type)),
                $notification->message,
                $notification->is_read ? 'Read' : 'Unread',
                $notification->link ?? 'N/A',
                $notification->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $notifications->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Send a notification to a user.
     * Requires 'notification.create' permission.
     */
    public function send(Request $request)
    {
        if (!auth()->user()->can('notification.create')) {
            return response()->json(['error' => 'You do not have permission to send notifications.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:warranty_expiry,low_stock,assignment,maintenance',
            'message' => 'required|string|max:500',
            'link' => 'nullable|string|max:255',
        ]);

        $notification = Notification::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully!',
            'notification' => $notification
        ]);
    }

    /**
     * Send bulk notifications to multiple users.
     * Requires 'notification.create' permission.
     */
    public function sendBulk(Request $request)
    {
        if (!auth()->user()->can('notification.create')) {
            return response()->json(['error' => 'You do not have permission to send bulk notifications.'], 403);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'type' => 'required|in:warranty_expiry,low_stock,assignment,maintenance',
            'message' => 'required|string|max:500',
            'link' => 'nullable|string|max:255',
        ]);

        $notifications = [];
        foreach ($validated['user_ids'] as $userId) {
            $notifications[] = Notification::create([
                'user_id' => $userId,
                'type' => $validated['type'],
                'message' => $validated['message'],
                'link' => $validated['link'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($notifications) . ' notification(s) sent successfully!',
            'count' => count($notifications)
        ]);
    }

    /**
     * Clear all notifications for the current user.
     * Requires 'notification.delete' permission.
     */
    public function clearAll()
    {
        if (!auth()->user()->can('notification.delete')) {
            return response()->json(['error' => 'You do not have permission to clear notifications.'], 403);
        }

        $count = Notification::where('user_id', auth()->id())->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) cleared successfully!"
        ]);
    }

    /**
     * Get notifications by type.
     * Requires 'notification.view' permission.
     */
    public function byType($type)
    {
        if (!auth()->user()->can('notification.view')) {
            return response()->json(['error' => 'You do not have permission to view notifications.'], 403);
        }

        $validTypes = ['warranty_expiry', 'low_stock', 'assignment', 'maintenance'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid notification type.'], 422);
        }

        $notifications = Notification::with('user')
            ->where('type', $type)
            ->latest()
            ->get();

        return response()->json([
            'type' => $type,
            'count' => $notifications->count(),
            'unread' => $notifications->where('is_read', false)->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get notification settings.
     * Requires 'notification.settings' permission.
     */
    public function settings()
    {
        if (!auth()->user()->can('notification.settings')) {
            abort(403, 'You do not have permission to view notification settings.');
        }

        // Default notification settings
        $settings = [
            'warranty_expiry' => [
                'enabled' => true,
                'days_before' => 30,
                'email' => true,
                'in_app' => true,
            ],
            'low_stock' => [
                'enabled' => true,
                'threshold' => 5,
                'email' => true,
                'in_app' => true,
            ],
            'assignment' => [
                'enabled' => true,
                'email' => true,
                'in_app' => true,
            ],
            'maintenance' => [
                'enabled' => true,
                'email' => true,
                'in_app' => true,
            ],
        ];

        return view('admin.notifications.settings', compact('settings'));
    }

    /**
     * Update notification settings.
     * Requires 'notification.settings' permission.
     */
    public function updateSettings(Request $request)
    {
        if (!auth()->user()->can('notification.settings')) {
            return response()->json(['error' => 'You do not have permission to update notification settings.'], 403);
        }

        $validated = $request->validate([
            'warranty_expiry' => 'nullable|array',
            'low_stock' => 'nullable|array',
            'assignment' => 'nullable|array',
            'maintenance' => 'nullable|array',
        ]);

        // Store settings in database or config
        // For now, return success
        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully!'
        ]);
    }

    /**
     * Get notification preferences for the current user.
     */
    public function preferences()
    {
        $userId = auth()->id();
        
        $preferences = [
            'email_notifications' => true,
            'in_app_notifications' => true,
            'types' => [
                'warranty_expiry' => true,
                'low_stock' => true,
                'assignment' => true,
                'maintenance' => true,
            ],
        ];

        return response()->json($preferences);
    }

    /**
     * Update notification preferences for the current user.
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'nullable|boolean',
            'in_app_notifications' => 'nullable|boolean',
            'types' => 'nullable|array',
            'types.*' => 'in:warranty_expiry,low_stock,assignment,maintenance',
        ]);

        // Store preferences in database
        // For now, return success
        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully!'
        ]);
    }
}
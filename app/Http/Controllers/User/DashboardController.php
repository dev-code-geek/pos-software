<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssetRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        
        $stats = [
            'my_assignments' => Assignment::where('user_id', $user->id)
                ->whereNull('actual_return_date')
                ->count(),
            'my_requests' => AssetRequest::where('user_id', $user->id)->count(),
            'pending_requests' => AssetRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
                ->count(),
            'overdue_assignments' => Assignment::where('user_id', $user->id)
                ->whereNull('actual_return_date')
                ->where('expected_return_date', '<', now())
                ->count(),
            'approved_requests' => AssetRequest::where('user_id', $user->id)
                ->where('status', 'approved_by_ict')
                ->count(),
            'rejected_requests' => AssetRequest::where('user_id', $user->id)
                ->where('status', 'rejected_by_ict')
                ->count(),
            'completed_requests' => AssetRequest::where('user_id', $user->id)
                ->where('status', 'assigned_to_user')
                ->count(),
        ];

        $myAssignments = Assignment::with('product')
            ->where('user_id', $user->id)
            ->whereNull('actual_return_date')
            ->latest()
            ->get();

        $recentRequests = AssetRequest::where('user_id', $user->id)
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        // Upcoming Returns
        $upcomingReturns = Assignment::where('user_id', $user->id)
            ->whereNull('actual_return_date')
            ->where('expected_return_date', '<=', now()->addDays(7))
            ->where('expected_return_date', '>', now())
            ->with('product')
            ->orderBy('expected_return_date', 'asc')
            ->get();

        $products = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->take(6)
            ->get();

        $categories = Category::all();

        // Notifications
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('user.dashboard.index', compact(
            'stats',
            'myAssignments',
            'recentRequests',
            'upcomingReturns',
            'products',
            'categories',
            'notifications',
            'unreadCount'
        ));
    }

    /**
     * Display all notifications for the user.
     * This method handles the notifications page.
     */
    public function notifications(Request $request)
    {
        $query = Notification::where('user_id', auth()->id());

        // Filter by read status
        if ($request->has('status')) {
            if ($request->status === 'read') {
                $query->where('is_read', true);
            } elseif ($request->status === 'unread') {
                $query->where('is_read', false);
            }
        }

        $notifications = $query->latest()->paginate(20);

        $unreadCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return view('user.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Display user's assignments.
     */
    public function myAssignments(Request $request)
    {
        $query = Assignment::where('user_id', auth()->id())
            ->with(['product', 'assignedBy']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNull('actual_return_date');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('actual_return_date');
            } elseif ($request->status === 'overdue') {
                $query->whereNull('actual_return_date')
                      ->where('expected_return_date', '<', now());
            }
        }

        $assignments = $query->latest('assigned_date')->paginate(15);

        $stats = [
            'active' => Assignment::where('user_id', auth()->id())->whereNull('actual_return_date')->count(),
            'overdue' => Assignment::where('user_id', auth()->id())->whereNull('actual_return_date')->where('expected_return_date', '<', now())->count(),
            'returned' => Assignment::where('user_id', auth()->id())->whereNotNull('actual_return_date')->count(),
            'total' => Assignment::where('user_id', auth()->id())->count(),
        ];

        return view('user.assignments.index', compact('assignments', 'stats'));
    }

    /**
     * Display assignment history.
     */
    public function assignmentHistory(Request $request)
    {
        $query = Assignment::where('user_id', auth()->id())
            ->whereNotNull('actual_return_date')
            ->with(['product']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $assignments = $query->latest('actual_return_date')->paginate(15);

        return view('user.assignments.history', compact('assignments'));
    }

    /**
     * Show a single assignment.
     */
    public function showAssignment($id)
    {
        $assignment = Assignment::where('user_id', auth()->id())
            ->with(['product', 'assignedBy'])
            ->findOrFail($id);

        return view('user.assignments.show', compact('assignment'));
    }

    /**
     * Return an assigned asset.
     */
    public function returnAssignment(Request $request, $id)
    {
        $assignment = Assignment::where('user_id', auth()->id())
            ->where('id', $id)
            ->whereNull('actual_return_date')
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'Assignment not found or already returned.'], 404);
        }

        $validated = $request->validate([
            'condition_in' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string|max:255',
        ]);

        $assignment->update([
            'actual_return_date' => now(),
            'condition_in' => $validated['condition_in'],
            'notes' => $validated['notes'] ?? $assignment->notes,
        ]);

        // Update product quantity
        $product = $assignment->product;
        $product->quantity += 1;
        if ($product->status === 'assigned' && $product->quantity > 0) {
            $product->status = 'available';
        }
        $product->save();

        // Notify admins
        $admins = \App\Models\User::role(['admin', 'ict_officer', 'super_admin'])->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'assignment',
                'message' => "Asset returned by " . auth()->user()->name . ": {$product->name}",
                'link' => route('admin.assignments.show', $assignment->id),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Asset returned successfully!'
        ]);
    }

    /**
     * Cancel an asset request.
     */
    public function cancelRequest($id)
    {
        $request = AssetRequest::where('user_id', auth()->id())
            ->where('id', $id)
            ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
            ->first();

        if (!$request) {
            return response()->json(['error' => 'Request not found or cannot be cancelled.'], 404);
        }

        $request->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Request cancelled successfully!'
        ]);
    }

    /**
     * Display available products.
     */
    public function products(Request $request)
    {
        $query = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->with(['category', 'supplier']);

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(20);
        $categories = Category::all();

        return view('user.products.index', compact('products', 'categories'));
    }

    /**
     * Display product details.
     */
    public function productDetail($id)
    {
        $product = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->with(['category', 'supplier'])
            ->findOrFail($id);

        $categories = Category::all();

        return view('user.products.show', compact('product', 'categories'));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.'
            ], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) marked as read.",
            'count' => $count
        ]);
    }
}
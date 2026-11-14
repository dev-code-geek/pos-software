<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('activity_log.view')) {
            abort(403, 'You do not have permission to view activity logs.');
        }

        $query = ActivityLog::with('user');

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by module
        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_name', 'LIKE', "%{$search}%")
                  ->orWhere('user_email', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('action', 'LIKE', "%{$search}%")
                  ->orWhere('module', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->per_page ?? 50;
        $logs = $query->orderBy('performed_at', 'desc')->paginate($perPage);

        // Stats
        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('performed_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('performed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ActivityLog::whereMonth('performed_at', now()->month)->whereYear('performed_at', now()->year)->count(),
            'unique_users' => ActivityLog::distinct('user_id')->count(),
            'by_action' => ActivityLog::select('action', \DB::raw('count(*) as total'))
                ->groupBy('action')
                ->pluck('total', 'action')
                ->toArray(),
            'by_module' => ActivityLog::select('module', \DB::raw('count(*) as total'))
                ->groupBy('module')
                ->pluck('total', 'module')
                ->toArray(),
        ];

        $users = User::all();
        $actions = ['login', 'logout', 'created', 'updated', 'deleted', 'viewed', 'exported', 'imported', 'assigned', 'returned', 'approved', 'rejected', 'forwarded', 'resolved'];
        $modules = ['auth', 'user', 'role', 'permission', 'product', 'category', 'supplier', 'department', 'assignment', 'asset_request', 'direct_request', 'purchase_order', 'procurement', 'inspection', 'maintenance', 'stock_movement', 'settings', 'report'];
        $statuses = ['success', 'failed', 'warning'];

        return view('admin.activity-logs.index', compact('logs', 'stats', 'users', 'actions', 'modules', 'statuses'));
    }

    /**
     * Display the specified activity log.
     */
    public function show($id)
    {
        if (!auth()->user()->can('activity_log.view')) {
            return response()->json(['error' => 'You do not have permission to view activity logs.'], 403);
        }

        $log = ActivityLog::with('user')->findOrFail($id);

        return response()->json([
            'log' => $log,
            'old_data' => $log->old_data ? json_decode($log->old_data, true) : null,
            'new_data' => $log->new_data ? json_decode($log->new_data, true) : null,
        ]);
    }

    /**
     * Clear activity logs.
     */
    public function clear(Request $request)
    {
        if (!auth()->user()->can('activity_log.delete')) {
            abort(403, 'You do not have permission to clear activity logs.');
        }

        $request->validate([
            'days' => 'nullable|integer|min:1',
        ]);

        $days = $request->days ?? 30;
        $deleted = ActivityLog::where('performed_at', '<', now()->subDays($days))->delete();

        return redirect()
            ->route('admin.activity-logs.index')
            ->with('success', "{$deleted} activity logs older than {$days} days cleared successfully.");
    }

    /**
     * Export activity logs.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('activity_log.view')) {
            return response()->json(['error' => 'You do not have permission to export activity logs.'], 403);
        }

        $logs = ActivityLog::with('user')
            ->orderBy('performed_at', 'desc')
            ->limit(10000)
            ->get();

        $exportData = [];
        $exportData[] = ['ID', 'User', 'Email', 'Role', 'Action', 'Module', 'Description', 'Status', 'IP Address', 'URL', 'Method', 'Duration', 'Performed At'];

        foreach ($logs as $log) {
            $exportData[] = [
                $log->id,
                $log->user_name ?? 'System',
                $log->user_email ?? 'N/A',
                $log->user_role ?? 'N/A',
                $log->action,
                $log->module,
                $log->description ?? 'N/A',
                $log->status,
                $log->ip_address ?? 'N/A',
                $log->url ?? 'N/A',
                $log->method ?? 'N/A',
                $log->duration_formatted ?? 'N/A',
                $log->performed_at->format('Y-m-d H:i:s'),
            ];
        }

        // For now return JSON, but you can implement Excel/CSV export
        return response()->json([
            'data' => $exportData,
            'count' => $logs->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get activity statistics for dashboard.
     */
    public function statistics()
    {
        if (!auth()->user()->can('activity_log.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('performed_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('performed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ActivityLog::whereMonth('performed_at', now()->month)->whereYear('performed_at', now()->year)->count(),
            'unique_users' => ActivityLog::distinct('user_id')->count(),
            'by_hour' => ActivityLog::select(\DB::raw('HOUR(performed_at) as hour'), \DB::raw('count(*) as total'))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
            'by_day' => ActivityLog::select(\DB::raw('DATE(performed_at) as day'), \DB::raw('count(*) as total'))
                ->where('performed_at', '>=', now()->subDays(30))
                ->groupBy('day')
                ->orderBy('day', 'desc')
                ->get(),
            'top_users' => ActivityLog::with('user')
                ->select('user_id', \DB::raw('count(*) as total'))
                ->groupBy('user_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }
}
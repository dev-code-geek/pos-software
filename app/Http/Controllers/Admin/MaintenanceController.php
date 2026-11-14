<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceLog;
use App\Models\Product;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of maintenance logs.
     * Requires 'maintenance.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('maintenance.view')) {
            abort(403, 'You do not have permission to view maintenance logs.');
        }

        $query = MaintenanceLog::with(['product', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('issue_description', 'LIKE', "%{$search}%")
                  ->orWhere('repaired_by', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function($p) use ($search) {
                      $p->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%");
                  });
            });
        }

        $logs = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total' => MaintenanceLog::count(),
            'reported' => MaintenanceLog::where('status', 'reported')->count(),
            'in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'fixed' => MaintenanceLog::where('status', 'fixed')->count(),
            'unrepairable' => MaintenanceLog::where('status', 'unrepairable')->count(),
            'critical' => MaintenanceLog::where('priority', 'critical')->count(),
            'high' => MaintenanceLog::where('priority', 'high')->count(),
            'today' => MaintenanceLog::whereDate('created_at', today())->count(),
            'total_cost' => MaintenanceLog::sum('cost'),
        ];

        $products = Product::all();
        $statuses = ['reported', 'in_progress', 'fixed', 'unrepairable'];
        $priorities = ['low', 'medium', 'high', 'critical'];

        return view('admin.maintenance.index', compact('logs', 'products', 'stats', 'statuses', 'priorities'));
    }

    /**
     * Show the form for creating a new maintenance log.
     * Requires 'maintenance.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('maintenance.create')) {
            abort(403, 'You do not have permission to create maintenance logs.');
        }

        $products = Product::all();
        return view('admin.maintenance.create', compact('products'));
    }

    /**
     * Store a newly created maintenance log.
     * Requires 'maintenance.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('maintenance.create')) {
            return response()->json(['error' => 'You do not have permission to create maintenance logs.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:reported,in_progress,fixed,unrepairable',
            'cost' => 'nullable|numeric|min:0',
            'repaired_by' => 'nullable|string|max:100',
            'repair_date' => 'nullable|date',
            'resolved_date' => 'nullable|date|after:repair_date',
        ]);

        $validated['user_id'] = auth()->id();
        $maintenance = MaintenanceLog::create($validated);

        // Update product status if maintenance is required
        if ($validated['status'] === 'reported' || $validated['status'] === 'in_progress') {
            $product = Product::find($validated['product_id']);
            if ($product->status !== 'under_repair') {
                $product->update(['status' => 'under_repair']);
            }
        }

        // Notify admin/ICT staff
        $adminUsers = User::role(['admin', 'ict_officer'])->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'maintenance',
                'message' => "New maintenance issue reported for product: {$product->name}",
                'link' => route('admin.maintenance.show', $maintenance->id),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Maintenance record created successfully!',
                'maintenance' => $maintenance
            ]);
        }

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance record created successfully!');
    }

    /**
     * Display the specified maintenance log.
     * Requires 'maintenance.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('maintenance.view')) {
            return response()->json(['error' => 'You do not have permission to view maintenance logs.'], 403);
        }

        $maintenance = MaintenanceLog::with(['product', 'user'])->findOrFail($id);
        return response()->json($maintenance);
    }

    /**
 * Show the form for editing the specified maintenance log.
 * Requires 'maintenance.edit' permission.
 */
public function edit($id)
{
    if (!auth()->user()->can('maintenance.edit')) {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit maintenance logs.'
            ], 403);
        }
        abort(403, 'You do not have permission to edit maintenance logs.');
    }

    try {
        $maintenance = MaintenanceLog::with(['product', 'user'])->findOrFail($id);
        
        // Log the data for debugging
        \Log::info('Maintenance edit data:', [
            'id' => $maintenance->id,
            'product_id' => $maintenance->product_id,
            'issue_description' => $maintenance->issue_description,
            'priority' => $maintenance->priority,
            'status' => $maintenance->status,
        ]);

        // For AJAX requests, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($maintenance);
        }

        // For normal view requests
        $products = Product::all();

        return view('admin.maintenance.edit', compact('maintenance', 'products'));
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::warning('Maintenance log not found for edit:', ['id' => $id]);
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Maintenance log not found.'
            ], 404);
        }
        abort(404, 'Maintenance log not found.');
        
    } catch (\Exception $e) {
        \Log::error('Edit maintenance error: ' . $e->getMessage(), [
            'id' => $id,
            'trace' => $e->getTraceAsString()
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load maintenance data: ' . $e->getMessage()
            ], 500);
        }
        abort(500, 'Failed to load maintenance data.');
    }
}

    /**
     * Update the specified maintenance log.
     * Requires 'maintenance.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('maintenance.edit')) {
            return response()->json(['error' => 'You do not have permission to edit maintenance logs.'], 403);
        }

        $maintenance = MaintenanceLog::findOrFail($id);
        $oldStatus = $maintenance->status;

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:reported,in_progress,fixed,unrepairable',
            'cost' => 'nullable|numeric|min:0',
            'repaired_by' => 'nullable|string|max:100',
            'repair_date' => 'nullable|date',
            'resolved_date' => 'nullable|date|after:repair_date',
        ]);

        $maintenance->update($validated);

        // Update product status based on new maintenance status
        $product = Product::find($validated['product_id']);
        if ($validated['status'] === 'fixed' || $validated['status'] === 'unrepairable') {
            // Check if product has any other unresolved maintenance issues
            $hasOtherIssues = MaintenanceLog::where('product_id', $product->id)
                ->whereIn('status', ['reported', 'in_progress'])
                ->where('id', '!=', $maintenance->id)
                ->exists();

            if (!$hasOtherIssues && $product->status === 'under_repair') {
                $product->update(['status' => 'available']);
            }
        } elseif ($product->status !== 'under_repair') {
            $product->update(['status' => 'under_repair']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Maintenance record updated successfully!',
                'maintenance' => $maintenance
            ]);
        }

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance record updated successfully!');
    }

    /**
     * Remove the specified maintenance log.
     * Requires 'maintenance.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('maintenance.delete')) {
            return response()->json(['error' => 'You do not have permission to delete maintenance logs.'], 403);
        }

        $maintenance = MaintenanceLog::findOrFail($id);
        $maintenance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance record deleted successfully!'
        ]);
    }

    /**
     * Bulk delete maintenance logs.
     * Requires 'maintenance.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('maintenance.delete')) {
            return response()->json(['error' => 'You do not have permission to delete maintenance logs.'], 403);
        }

        $request->validate([
            'maintenance_ids' => 'required|array|min:1',
            'maintenance_ids.*' => 'exists:maintenance_logs,id',
        ]);

        $deletedCount = MaintenanceLog::whereIn('id', $request->maintenance_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} maintenance record(s) deleted successfully!"
        ]);
    }

    /**
     * Resolve a maintenance issue.
     * Requires 'maintenance.edit' permission.
     */
    public function resolve(Request $request, $id)
    {
        if (!auth()->user()->can('maintenance.edit')) {
            return response()->json(['error' => 'You do not have permission to resolve maintenance issues.'], 403);
        }

        $maintenance = MaintenanceLog::findOrFail($id);

        if ($maintenance->status === 'fixed' || $maintenance->status === 'unrepairable') {
            return response()->json(['error' => 'This maintenance issue is already resolved.'], 422);
        }

        $validated = $request->validate([
            'resolved_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'repaired_by' => 'nullable|string|max:100',
        ]);

        $oldStatus = $maintenance->status;
        $maintenance->update([
            'status' => 'fixed',
            'resolved_date' => $validated['resolved_date'],
            'cost' => $validated['cost'] ?? $maintenance->cost,
            'repaired_by' => $validated['repaired_by'] ?? $maintenance->repaired_by,
            'notes' => $validated['notes'] ? ($maintenance->notes ? $maintenance->notes . "\n" : '') . "Resolution notes: " . $validated['notes'] : $maintenance->notes,
        ]);

        // Check if product has any other unresolved issues
        $product = $maintenance->product;
        $hasOtherIssues = MaintenanceLog::where('product_id', $product->id)
            ->whereIn('status', ['reported', 'in_progress'])
            ->where('id', '!=', $maintenance->id)
            ->exists();

        if (!$hasOtherIssues && $product->status === 'under_repair') {
            $product->update(['status' => 'available']);
        }

        // Notify the reporter
        Notification::create([
            'user_id' => $maintenance->user_id,
            'type' => 'maintenance',
            'message' => "Maintenance issue for {$product->name} has been resolved.",
            'link' => route('admin.maintenance.show', $maintenance->id),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance issue resolved successfully!'
        ]);
    }

    /**
     * Mark a maintenance issue as unrepairable.
     * Requires 'maintenance.edit' permission.
     */
    public function markUnrepairable(Request $request, $id)
    {
        if (!auth()->user()->can('maintenance.edit')) {
            return response()->json(['error' => 'You do not have permission to mark as unrepairable.'], 403);
        }

        $maintenance = MaintenanceLog::findOrFail($id);

        $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $maintenance->update([
            'status' => 'unrepairable',
            'notes' => ($maintenance->notes ? $maintenance->notes . "\n" : '') . "Unrepairable reason: " . $request->reason,
        ]);

        // Update product status
        $product = $maintenance->product;
        $product->update(['status' => 'retired']);

        // Notify the reporter
        Notification::create([
            'user_id' => $maintenance->user_id,
            'type' => 'maintenance',
            'message' => "{$product->name} has been marked as unrepairable. Reason: {$request->reason}",
            'link' => route('admin.maintenance.show', $maintenance->id),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance issue marked as unrepairable.'
        ]);
    }

    /**
     * Get maintenance statistics.
     * Requires 'maintenance.view' permission.
     */
    public function statistics(Request $request)
    {
        if (!auth()->user()->can('maintenance.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $period = $request->period ?? 'all';

        $stats = [
            'total' => MaintenanceLog::count(),
            'by_status' => MaintenanceLog::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'by_priority' => MaintenanceLog::select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->toArray(),
            'total_cost' => MaintenanceLog::sum('cost'),
            'average_cost' => MaintenanceLog::avg('cost'),
            'today' => MaintenanceLog::whereDate('created_at', today())->count(),
            'this_week' => MaintenanceLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => MaintenanceLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'resolution_rate' => MaintenanceLog::count() > 0
                ? round((MaintenanceLog::where('status', 'fixed')->count() / MaintenanceLog::count()) * 100, 2)
                : 0,
            'top_products' => MaintenanceLog::with('product')
                ->select('product_id', DB::raw('count(*) as total'))
                ->groupBy('product_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentIssues = MaintenanceLog::with(['product', 'user'])
            ->whereIn('status', ['reported', 'in_progress'])
            ->latest()
            ->limit(5)
            ->get();

        $resolvedToday = MaintenanceLog::whereDate('resolved_date', today())
            ->where('status', 'fixed')
            ->count();

        $criticalIssues = MaintenanceLog::where('priority', 'critical')
            ->whereIn('status', ['reported', 'in_progress'])
            ->count();

        return response()->json([
            'recent_issues' => $recentIssues,
            'resolved_today' => $resolvedToday,
            'critical_issues' => $criticalIssues,
            'total_reported' => MaintenanceLog::where('status', 'reported')->count(),
            'total_in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'total_fixed' => MaintenanceLog::where('status', 'fixed')->count(),
        ]);
    }

    /**
     * Export maintenance logs to CSV/Excel.
     * Requires 'maintenance.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('maintenance.view')) {
            return response()->json(['error' => 'You do not have permission to export maintenance logs.'], 403);
        }

        $logs = MaintenanceLog::with(['product', 'user'])
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'Product', 'Reported By', 'Issue Description', 'Priority',
            'Status', 'Cost', 'Repaired By', 'Repair Date', 'Resolved Date',
            'Created At'
        ];

        foreach ($logs as $log) {
            $exportData[] = [
                $log->id,
                $log->product->name ?? 'N/A',
                $log->user->name ?? 'N/A',
                $log->issue_description,
                ucfirst($log->priority),
                ucfirst(str_replace('_', ' ', $log->status)),
                $log->cost ?? '0.00',
                $log->repaired_by ?? 'N/A',
                $log->repair_date ? $log->repair_date->format('Y-m-d') : 'N/A',
                $log->resolved_date ? $log->resolved_date->format('Y-m-d') : 'N/A',
                $log->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $logs->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Generate maintenance report.
     * Requires 'maintenance.view' permission.
     */
    public function report(Request $request)
    {
        if (!auth()->user()->can('maintenance.view')) {
            return response()->json(['error' => 'You do not have permission to view reports.'], 403);
        }

        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now();

        $report = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])->count(),
                'reported' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'reported')
                    ->count(),
                'in_progress' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'in_progress')
                    ->count(),
                'fixed' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'fixed')
                    ->count(),
                'unrepairable' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'unrepairable')
                    ->count(),
                'total_cost' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])->sum('cost'),
            ],
            'by_priority' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->toArray(),
            'top_products' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                ->with('product')
                ->select('product_id', DB::raw('count(*) as total'))
                ->groupBy('product_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'resolution_time_avg' => MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('resolved_date')
                ->get()
                ->avg(function($log) {
                    return $log->created_at->diffInHours($log->resolved_date);
                }),
        ];

        return response()->json($report);
    }

    /**
     * Get maintenance logs for a specific product.
     * Requires 'maintenance.view' permission.
     */
    public function byProduct($productId)
    {
        if (!auth()->user()->can('maintenance.view')) {
            return response()->json(['error' => 'You do not have permission to view maintenance logs.'], 403);
        }

        $logs = MaintenanceLog::with(['user'])
            ->where('product_id', $productId)
            ->latest()
            ->get();

        $product = Product::find($productId);

        return response()->json([
            'product' => $product->name ?? 'Unknown Product',
            'total' => $logs->count(),
            'logs' => $logs,
        ]);
    }

    /**
     * Get unresolved maintenance issues.
     * Requires 'maintenance.view' permission.
     */
    public function unresolved()
    {
        if (!auth()->user()->can('maintenance.view')) {
            return response()->json(['error' => 'You do not have permission to view maintenance logs.'], 403);
        }

        $logs = MaintenanceLog::with(['product', 'user'])
            ->whereIn('status', ['reported', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json([
            'total' => $logs->count(),
            'logs' => $logs,
        ]);
    }

    /**
     * Get maintenance summary for dashboard.
     */
    public function summary()
    {
        $summary = [
            'total_reported' => MaintenanceLog::where('status', 'reported')->count(),
            'in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'fixed' => MaintenanceLog::where('status', 'fixed')->count(),
            'unrepairable' => MaintenanceLog::where('status', 'unrepairable')->count(),
            'critical' => MaintenanceLog::where('priority', 'critical')
                ->whereIn('status', ['reported', 'in_progress'])
                ->count(),
            'high' => MaintenanceLog::where('priority', 'high')
                ->whereIn('status', ['reported', 'in_progress'])
                ->count(),
            'total_cost' => MaintenanceLog::sum('cost'),
            'avg_response_time' => MaintenanceLog::whereNotNull('resolved_date')
                ->get()
                ->avg(function($log) {
                    return $log->created_at->diffInHours($log->resolved_date);
                }),
        ];

        return response()->json($summary);
    }

    /**
     * Assign maintenance to a technician.
     * Requires 'maintenance.edit' permission.
     */
    public function assign(Request $request, $id)
    {
        if (!auth()->user()->can('maintenance.edit')) {
            return response()->json(['error' => 'You do not have permission to assign maintenance.'], 403);
        }

        $request->validate([
            'repaired_by' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $maintenance = MaintenanceLog::findOrFail($id);

        $maintenance->update([
            'repaired_by' => $request->repaired_by,
            'status' => 'in_progress',
            'notes' => $request->notes ? ($maintenance->notes ? $maintenance->notes . "\n" : '') . "Assigned to: " . $request->repaired_by . "\nNotes: " . $request->notes : $maintenance->notes,
        ]);

        // Update product status if not already under repair
        if ($maintenance->product->status !== 'under_repair') {
            $maintenance->product->update(['status' => 'under_repair']);
        }

        return response()->json([
            'success' => true,
            'message' => "Maintenance assigned to {$request->repaired_by} successfully!"
        ]);
    }

    /**
     * Add note to maintenance log.
     * Requires 'maintenance.edit' permission.
     */
    public function addNote(Request $request, $id)
    {
        if (!auth()->user()->can('maintenance.edit')) {
            return response()->json(['error' => 'You do not have permission to add notes.'], 403);
        }

        $request->validate([
            'note' => 'required|string',
        ]);

        $maintenance = MaintenanceLog::findOrFail($id);

        $maintenance->update([
            'notes' => ($maintenance->notes ? $maintenance->notes . "\n" : '') . 
                       date('Y-m-d H:i:s') . " - " . auth()->user()->name . ": " . $request->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully!'
        ]);
    }

    /**
     * Get maintenance by priority.
     * Requires 'maintenance.view' permission.
     */
    public function byPriority($priority)
    {
        if (!auth()->user()->can('maintenance.view')) {
            return response()->json(['error' => 'You do not have permission to view maintenance logs.'], 403);
        }

        $validPriorities = ['low', 'medium', 'high', 'critical'];
        
        if (!in_array($priority, $validPriorities)) {
            return response()->json(['error' => 'Invalid priority.'], 422);
        }

        $logs = MaintenanceLog::with(['product', 'user'])
            ->where('priority', $priority)
            ->latest()
            ->get();

        return response()->json([
            'priority' => $priority,
            'count' => $logs->count(),
            'logs' => $logs,
        ]);
    }
}
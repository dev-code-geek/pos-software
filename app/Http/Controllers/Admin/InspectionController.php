<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\AssetRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
use App\Models\RequestWorkflow;
use App\Models\Notification;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    /**
     * Display a listing of inspections.
     * Requires 'inspection.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('inspection.view')) {
            abort(403, 'You do not have permission to view inspections.');
        }

        $query = Inspection::with(['assetRequest', 'product', 'inspectedBy', 'returnedToSupplier']);

        // Filter by result
        if ($request->has('result') && $request->result) {
            $query->where('result', $request->result);
        }

        // Filter by status (returned or not)
        if ($request->has('returned')) {
            if ($request->returned === 'yes') {
                $query->where('is_returned', true);
            } elseif ($request->returned === 'no') {
                $query->where('is_returned', false);
            }
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($p) use ($search) {
                    $p->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                })->orWhereHas('assetRequest', function($a) use ($search) {
                    $a->where('title', 'LIKE', "%{$search}%");
                })->orWhere('issues_found', 'LIKE', "%{$search}%");
            });
        }

        $inspections = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total' => Inspection::count(),
            'passed' => Inspection::where('result', 'passed')->count(),
            'failed' => Inspection::where('result', 'failed')->count(),
            'partial' => Inspection::where('result', 'partial')->count(),
            'returned' => Inspection::where('is_returned', true)->count(),
            'today' => Inspection::whereDate('inspection_date', today())->count(),
            'this_week' => Inspection::whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
         
        $assetRequests = AssetRequest::whereIn('status', ['under_inspection', 'pending_ict_review', 'approved_by_ict'])->get();
        $suppliers = Supplier::all();

        // Get products for filter
        $products = Product::all();

        return view('admin.inspections.index', compact('inspections', 'stats', 'products', 'assetRequests', 'suppliers'));
    }

    /**
     * Show the form for creating a new inspection.
     * Requires 'inspection.create' permission.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('inspection.create')) {
            abort(403, 'You do not have permission to create inspections.');
        }

        $assetRequestId = $request->asset_request_id;
        $assetRequest = null;
        $products = [];

        if ($assetRequestId) {
            $assetRequest = AssetRequest::with(['user', 'category'])->find($assetRequestId);
            $products = Product::where('status', 'available')
                ->orWhere('status', 'under_inspection')
                ->get();
        } else {
            $products = Product::whereIn('status', ['available', 'under_inspection'])->get();
        }

        $inspectors = User::role(['admin', 'ict_officer'])->get();
        $suppliers = Supplier::all();

        return view('admin.inspections.create', compact('assetRequest', 'products', 'inspectors', 'suppliers'));
    }

    /**
     * Store a newly created inspection.
     * Requires 'inspection.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('inspection.create')) {
            return response()->json(['error' => 'You do not have permission to create inspections.'], 403);
        }

        $validated = $request->validate([
            'asset_request_id' => 'required|exists:asset_requests,id',
            'product_id' => 'required|exists:products,id',
            'inspection_date' => 'required|date',
            'result' => 'required|in:passed,failed,partial',
            'physical_condition' => 'required|in:new,excellent,good,fair,poor,damaged',
            'functional_condition' => 'required|in:working,minor_issues,major_issues,non_functional',
            'issues_found' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'is_returned' => 'nullable|boolean',
            'return_reason' => 'nullable|string|required_if:is_returned,true',
            'return_date' => 'nullable|date',
            'returned_to_supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $validated['inspected_by'] = auth()->id();
        $validated['is_returned'] = $validated['is_returned'] ?? false;

        $inspection = Inspection::create($validated);

        // Update asset request status
        $assetRequest = AssetRequest::find($validated['asset_request_id']);
        $oldStatus = $assetRequest->status;
        
        if ($validated['result'] === 'passed') {
            $assetRequest->update(['status' => 'inspection_passed']);
            
            // Create assignment if not already assigned
            $this->createAssignmentFromInspection($inspection);
        } elseif ($validated['result'] === 'failed') {
            $assetRequest->update(['status' => 'inspection_failed']);
        } else {
            $assetRequest->update(['status' => 'under_inspection']);
        }

        // Update product status
        $product = Product::find($validated['product_id']);
        if ($validated['result'] === 'passed') {
            $product->update(['status' => 'available']);
        } elseif ($validated['result'] === 'failed') {
            $product->update(['status' => 'under_repair']);
        }

        // Create workflow entry
        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'procurement',
            'action' => 'inspected',
            'status_before' => $oldStatus,
            'status_after' => $assetRequest->status,
            'comments' => "Inspection result: {$validated['result']}. " . ($validated['issues_found'] ?? 'No issues found.'),
        ]);

        // If returned to supplier, handle return
        if ($validated['is_returned'] && $validated['returned_to_supplier_id']) {
            $this->handleReturnToSupplier($inspection);
        }

        // Notify the user
        Notification::create([
            'user_id' => $assetRequest->user_id,
            'type' => 'assignment',
            'message' => "Inspection completed for your request #{$assetRequest->id}. Result: " . ucfirst($validated['result']),
            'link' => route('admin.inspections.show', $inspection->id),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inspection created successfully!',
                'inspection' => $inspection
            ]);
        }

        return redirect()->route('admin.inspections.index')
            ->with('success', 'Inspection created successfully!');
    }

    /**
     * Display the specified inspection.
     * Requires 'inspection.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('inspection.view')) {
            return response()->json(['error' => 'You do not have permission to view inspections.'], 403);
        }

        $inspection = Inspection::with([
            'assetRequest' => function($q) {
                $q->with(['user', 'category']);
            },
            'product' => function($q) {
                $q->with(['category', 'supplier']);
            },
            'inspectedBy',
            'returnedToSupplier'
        ])->findOrFail($id);

        // Get related workflows
        $workflows = RequestWorkflow::where('asset_request_id', $inspection->asset_request_id)
            ->with('actor')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'inspection' => $inspection,
            'workflows' => $workflows,
        ]);
    }

    
    /**
     * Show the form for editing the specified inspection.
     * Requires 'inspection.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('inspection.edit')) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit inspections.'
                ], 403);
            }
            abort(403, 'You do not have permission to edit inspections.');
        }

        try {
            $inspection = Inspection::with(['product', 'assetRequest'])->findOrFail($id);
            
            // Log the data for debugging
            \Log::info('Inspection edit data:', [
                'id' => $inspection->id,
                'product_id' => $inspection->product_id,
                'asset_request_id' => $inspection->asset_request_id,
                'result' => $inspection->result,
            ]);

            // For AJAX requests, return JSON
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($inspection);
            }

            // For normal view requests
            $products = Product::all();
            $inspectors = User::role(['admin', 'ict_officer'])->get();
            $suppliers = Supplier::all();

            return view('admin.inspections.edit', compact('inspection', 'products', 'inspectors', 'suppliers'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Inspection not found for edit:', ['id' => $id]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Inspection not found.'
                ], 404);
            }
            abort(404, 'Inspection not found.');
            
        } catch (\Exception $e) {
            \Log::error('Edit inspection error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load inspection data: ' . $e->getMessage()
                ], 500);
            }
            abort(500, 'Failed to load inspection data.');
        }
    }


    /**
     * Update the specified inspection.
     * Requires 'inspection.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('inspection.edit')) {
            return response()->json(['error' => 'You do not have permission to edit inspections.'], 403);
        }

        $inspection = Inspection::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'inspection_date' => 'required|date',
            'result' => 'required|in:passed,failed,partial',
            'physical_condition' => 'required|in:new,excellent,good,fair,poor,damaged',
            'functional_condition' => 'required|in:working,minor_issues,major_issues,non_functional',
            'issues_found' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'is_returned' => 'nullable|boolean',
            'return_reason' => 'nullable|string|required_if:is_returned,true',
            'return_date' => 'nullable|date',
            'returned_to_supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $validated['is_returned'] = $validated['is_returned'] ?? false;

        $inspection->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inspection updated successfully!',
                'inspection' => $inspection
            ]);
        }

        return redirect()->route('admin.inspections.index')
            ->with('success', 'Inspection updated successfully!');
    }

    /**
     * Remove the specified inspection.
     * Requires 'inspection.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('inspection.delete')) {
            return response()->json(['error' => 'You do not have permission to delete inspections.'], 403);
        }

        $inspection = Inspection::findOrFail($id);
        $inspection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inspection deleted successfully!'
        ]);
    }

    /**
     * Bulk delete inspections.
     * Requires 'inspection.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('inspection.delete')) {
            return response()->json(['error' => 'You do not have permission to delete inspections.'], 403);
        }

        $request->validate([
            'inspection_ids' => 'required|array|min:1',
            'inspection_ids.*' => 'exists:inspections,id',
        ]);

        $deletedCount = Inspection::whereIn('id', $request->inspection_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} inspection(s) deleted successfully!"
        ]);
    }

    /**
     * Pass an inspection (batch operation).
     * Requires 'inspection.pass' permission.
     */
    public function pass(Request $request, $id)
    {
        if (!auth()->user()->can('inspection.pass')) {
            return response()->json(['error' => 'You do not have permission to pass inspections.'], 403);
        }

        $inspection = Inspection::findOrFail($id);
        
        if ($inspection->result !== 'failed') {
            $inspection->update(['result' => 'passed']);
        }

        // Update asset request
        $assetRequest = AssetRequest::find($inspection->asset_request_id);
        $oldStatus = $assetRequest->status;
        $assetRequest->update(['status' => 'inspection_passed']);

        // Create assignment
        $this->createAssignmentFromInspection($inspection);

        // Update product status
        $product = Product::find($inspection->product_id);
        $product->update(['status' => 'available']);

        RequestWorkflow::create([
            'asset_request_id' => $inspection->asset_request_id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'user',
            'action' => 'passed_inspection',
            'status_before' => $oldStatus,
            'status_after' => 'inspection_passed',
            'comments' => 'Inspection passed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inspection passed successfully!'
        ]);
    }

    /**
     * Fail an inspection (batch operation).
     * Requires 'inspection.fail' permission.
     */
    public function fail(Request $request, $id)
    {
        if (!auth()->user()->can('inspection.fail')) {
            return response()->json(['error' => 'You do not have permission to fail inspections.'], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $inspection = Inspection::findOrFail($id);
        
        $inspection->update([
            'result' => 'failed',
            'issues_found' => $request->reason,
        ]);

        // Update asset request
        $assetRequest = AssetRequest::find($inspection->asset_request_id);
        $oldStatus = $assetRequest->status;
        $assetRequest->update(['status' => 'inspection_failed']);

        // Update product status
        $product = Product::find($inspection->product_id);
        $product->update(['status' => 'under_repair']);

        RequestWorkflow::create([
            'asset_request_id' => $inspection->asset_request_id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'procurement',
            'action' => 'failed_inspection',
            'status_before' => $oldStatus,
            'status_after' => 'inspection_failed',
            'comments' => "Inspection failed: {$request->reason}",
        ]);

        // Notify procurement
        $procurementStaff = User::role('procurement_officer')->get();
        foreach ($procurementStaff as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'assignment',
                'message' => "Inspection failed for request #{$assetRequest->id}. Reason: {$request->reason}",
                'link' => route('admin.inspections.show', $inspection->id),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inspection failed successfully!'
        ]);
    }

    /**
     * Bulk pass inspections.
     * Requires 'inspection.pass' permission.
     */
    public function bulkPass(Request $request)
    {
        if (!auth()->user()->can('inspection.pass')) {
            return response()->json(['error' => 'You do not have permission to pass inspections.'], 403);
        }

        $request->validate([
            'inspection_ids' => 'required|array|min:1',
            'inspection_ids.*' => 'exists:inspections,id',
        ]);

        $inspections = Inspection::whereIn('id', $request->inspection_ids)
            ->where('result', '!=', 'passed')
            ->get();

        foreach ($inspections as $inspection) {
            $inspection->update(['result' => 'passed']);
            
            // Update asset request
            $assetRequest = AssetRequest::find($inspection->asset_request_id);
            if ($assetRequest) {
                $assetRequest->update(['status' => 'inspection_passed']);
                $this->createAssignmentFromInspection($inspection);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($inspections) . ' inspection(s) passed successfully!'
        ]);
    }

    /**
     * Bulk fail inspections.
     * Requires 'inspection.fail' permission.
     */
    public function bulkFail(Request $request)
    {
        if (!auth()->user()->can('inspection.fail')) {
            return response()->json(['error' => 'You do not have permission to fail inspections.'], 403);
        }

        $request->validate([
            'inspection_ids' => 'required|array|min:1',
            'inspection_ids.*' => 'exists:inspections,id',
            'reason' => 'required|string|min:5',
        ]);

        $inspections = Inspection::whereIn('id', $request->inspection_ids)
            ->where('result', '!=', 'failed')
            ->get();

        foreach ($inspections as $inspection) {
            $inspection->update([
                'result' => 'failed',
                'issues_found' => $request->reason,
            ]);

            // Update asset request
            $assetRequest = AssetRequest::find($inspection->asset_request_id);
            if ($assetRequest) {
                $assetRequest->update(['status' => 'inspection_failed']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($inspections) . ' inspection(s) failed successfully!'
        ]);
    }

    /**
     * Return inspected product to supplier.
     * Requires 'inspection.return_to_supplier' permission.
     */
    public function returnToSupplier(Request $request, $id)
    {
        if (!auth()->user()->can('inspection.return_to_supplier')) {
            return response()->json(['error' => 'You do not have permission to return to supplier.'], 403);
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'return_reason' => 'required|string|min:5',
            'return_date' => 'required|date',
        ]);

        $inspection = Inspection::findOrFail($id);

        $inspection->update([
            'is_returned' => true,
            'returned_to_supplier_id' => $request->supplier_id,
            'return_reason' => $request->return_reason,
            'return_date' => $request->return_date,
        ]);

        // Update product status
        $product = Product::find($inspection->product_id);
        $product->update(['status' => 'retired']);

        // Update asset request
        $assetRequest = AssetRequest::find($inspection->asset_request_id);
        if ($assetRequest) {
            $assetRequest->update(['status' => 'inspection_failed']);
        }

        // Record stock movement
        StockMovement::create([
            'product_id' => $inspection->product_id,
            'user_id' => auth()->id(),
            'type' => 'return',
            'quantity_change' => -$product->quantity,
            'previous_quantity' => $product->quantity,
            'new_quantity' => 0,
            'reference' => 'RETURN-SUPPLIER-' . $inspection->id,
            'notes' => "Returned to supplier: {$request->return_reason}",
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $inspection->asset_request_id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'supplier',
            'action' => 'returned_to_supplier',
            'status_after' => 'inspection_failed',
            'comments' => "Returned to supplier. Reason: {$request->return_reason}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product returned to supplier successfully!'
        ]);
    }

    /**
     * Export inspections to CSV/Excel.
     * Requires 'inspection.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('inspection.view')) {
            return response()->json(['error' => 'You do not have permission to export inspections.'], 403);
        }

        $inspections = Inspection::with(['product', 'assetRequest', 'inspectedBy'])
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'Product', 'Request ID', 'Inspected By', 'Date',
            'Result', 'Physical Condition', 'Functional Condition',
            'Issues Found', 'Returned', 'Return Date', 'Created At'
        ];

        foreach ($inspections as $inspection) {
            $exportData[] = [
                $inspection->id,
                $inspection->product->name ?? 'N/A',
                $inspection->asset_request_id ?? 'N/A',
                $inspection->inspectedBy->name ?? 'N/A',
                $inspection->inspection_date->format('Y-m-d'),
                ucfirst($inspection->result),
                ucfirst(str_replace('_', ' ', $inspection->physical_condition)),
                ucfirst(str_replace('_', ' ', $inspection->functional_condition)),
                $inspection->issues_found ?? 'None',
                $inspection->is_returned ? 'Yes' : 'No',
                $inspection->return_date ? $inspection->return_date->format('Y-m-d') : 'N/A',
                $inspection->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $inspections->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get inspection statistics.
     * Requires 'inspection.view' permission.
     */
    public function statistics(Request $request)
    {
        if (!auth()->user()->can('inspection.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $period = $request->period ?? 'all';

        $stats = [
            'total' => Inspection::count(),
            'by_result' => Inspection::select('result', DB::raw('count(*) as total'))
                ->groupBy('result')
                ->pluck('total', 'result')
                ->toArray(),
            'by_physical_condition' => Inspection::select('physical_condition', DB::raw('count(*) as total'))
                ->groupBy('physical_condition')
                ->pluck('total', 'physical_condition')
                ->toArray(),
            'by_functional_condition' => Inspection::select('functional_condition', DB::raw('count(*) as total'))
                ->groupBy('functional_condition')
                ->pluck('total', 'functional_condition')
                ->toArray(),
            'returned_count' => Inspection::where('is_returned', true)->count(),
            'returned_rate' => Inspection::count() > 0 
                ? round((Inspection::where('is_returned', true)->count() / Inspection::count()) * 100, 2)
                : 0,
            'passed_rate' => Inspection::count() > 0 
                ? round((Inspection::where('result', 'passed')->count() / Inspection::count()) * 100, 2)
                : 0,
            'failed_rate' => Inspection::count() > 0 
                ? round((Inspection::where('result', 'failed')->count() / Inspection::count()) * 100, 2)
                : 0,
            'today' => Inspection::whereDate('inspection_date', today())->count(),
            'this_week' => Inspection::whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Inspection::whereMonth('inspection_date', now()->month)
                ->whereYear('inspection_date', now()->year)
                ->count(),
        ];

        // Monthly trends
        $monthlyTrends = Inspection::select(
                DB::raw('DATE_FORMAT(inspection_date, "%Y-%m") as month'),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN result = "passed" THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN result = "failed" THEN 1 ELSE 0 END) as failed')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->limit(12)
            ->get();

        return response()->json([
            'stats' => $stats,
            'monthly_trends' => $monthlyTrends,
        ]);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentInspections = Inspection::with(['product', 'assetRequest', 'inspectedBy'])
            ->latest()
            ->limit(5)
            ->get();

        $pendingInspections = Inspection::where('result', 'partial')
            ->with(['product', 'assetRequest'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'recent_inspections' => $recentInspections,
            'pending_inspections' => $pendingInspections,
            'total_today' => Inspection::whereDate('inspection_date', today())->count(),
            'passed_today' => Inspection::whereDate('inspection_date', today())
                ->where('result', 'passed')
                ->count(),
            'failed_today' => Inspection::whereDate('inspection_date', today())
                ->where('result', 'failed')
                ->count(),
        ]);
    }

    /**
     * Get inspections by product.
     * Requires 'inspection.view' permission.
     */
    public function byProduct($productId)
    {
        if (!auth()->user()->can('inspection.view')) {
            return response()->json(['error' => 'You do not have permission to view inspections.'], 403);
        }

        $inspections = Inspection::with(['assetRequest', 'inspectedBy'])
            ->where('product_id', $productId)
            ->latest()
            ->get();

        return response()->json([
            'product_id' => $productId,
            'count' => $inspections->count(),
            'inspections' => $inspections,
        ]);
    }

    /**
     * Get inspection report.
     * Requires 'inspection.view' permission.
     */
    public function report(Request $request)
    {
        if (!auth()->user()->can('inspection.view')) {
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
                'total' => Inspection::whereBetween('inspection_date', [$startDate, $endDate])->count(),
                'passed' => Inspection::whereBetween('inspection_date', [$startDate, $endDate])
                    ->where('result', 'passed')
                    ->count(),
                'failed' => Inspection::whereBetween('inspection_date', [$startDate, $endDate])
                    ->where('result', 'failed')
                    ->count(),
                'partial' => Inspection::whereBetween('inspection_date', [$startDate, $endDate])
                    ->where('result', 'partial')
                    ->count(),
                'returned' => Inspection::whereBetween('return_date', [$startDate, $endDate])
                    ->where('is_returned', true)
                    ->count(),
            ],
            'by_physical_condition' => Inspection::whereBetween('inspection_date', [$startDate, $endDate])
                ->select('physical_condition', DB::raw('count(*) as total'))
                ->groupBy('physical_condition')
                ->pluck('total', 'physical_condition')
                ->toArray(),
            'by_functional_condition' => Inspection::whereBetween('inspection_date', [$startDate, $endDate])
                ->select('functional_condition', DB::raw('count(*) as total'))
                ->groupBy('functional_condition')
                ->pluck('total', 'functional_condition')
                ->toArray(),
            'top_products' => Inspection::whereBetween('inspection_date', [$startDate, $endDate])
                ->with('product')
                ->select('product_id', DB::raw('count(*) as total'))
                ->groupBy('product_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($report);
    }

    /**
     * Create assignment from inspection.
     * Helper method.
     */
    private function createAssignmentFromInspection(Inspection $inspection)
    {
        $assetRequest = AssetRequest::find($inspection->asset_request_id);
        
        if (!$assetRequest) {
            return;
        }

        // Check if assignment already exists
        $existingAssignment = \App\Models\Assignment::where('product_id', $inspection->product_id)
            ->where('user_id', $assetRequest->user_id)
            ->whereNull('actual_return_date')
            ->first();

        if (!$existingAssignment) {
            \App\Models\Assignment::create([
                'product_id' => $inspection->product_id,
                'user_id' => $assetRequest->user_id,
                'assigned_by' => auth()->id(),
                'assigned_date' => now(),
                'condition_out' => 'new',
                'notes' => 'Assigned after passing inspection',
            ]);

            // Update asset request status
            $assetRequest->update([
                'status' => 'assigned_to_user',
                'assigned_product_id' => $inspection->product_id,
            ]);

            // Update product status
            $product = Product::find($inspection->product_id);
            $product->quantity -= 1;
            if ($product->quantity == 0) {
                $product->status = 'assigned';
            }
            $product->save();

            // Notify the user
            Notification::create([
                'user_id' => $assetRequest->user_id,
                'type' => 'assignment',
                'message' => "Asset has been assigned to you after inspection",
                'link' => route('user.my-assignments'),
            ]);
        }
    }

    /**
     * Handle return to supplier.
     * Helper method.
     */
    private function handleReturnToSupplier(Inspection $inspection)
    {
        $product = Product::find($inspection->product_id);
        
        // Update product quantity
        $product->quantity = 0;
        $product->status = 'retired';
        $product->save();

        // Record stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'return',
            'quantity_change' => -$product->quantity,
            'previous_quantity' => $product->quantity,
            'new_quantity' => 0,
            'reference' => 'RETURN-SUPPLIER-' . $inspection->id,
            'notes' => "Returned to supplier: {$inspection->return_reason}",
        ]);

        // Notify procurement
        $procurementStaff = User::role('procurement_officer')->get();
        foreach ($procurementStaff as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'assignment',
                'message' => "Product returned to supplier: {$product->name}. Reason: {$inspection->return_reason}",
                'link' => route('admin.inspections.show', $inspection->id),
            ]);
        }
    }

    /**
     * Get asset requests for dropdown.
     */
    public function getAssetRequests()
    {
        $assetRequests = AssetRequest::whereIn('status', ['under_inspection', 'pending_ict_review', 'approved_by_ict'])
            ->latest()
            ->get(['id', 'title']);
        
        return response()->json($assetRequests);
    }
}
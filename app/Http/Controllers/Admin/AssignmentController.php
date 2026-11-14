<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Product;
use App\Models\User;
use App\Models\Notification;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     * Requires 'assignment.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('assignment.view')) {
            abort(403, 'You do not have permission to view assignments.');
        }

        $query = Assignment::with(['product', 'user', 'assignedBy']);

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

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
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
                })->orWhereHas('user', function($u) use ($search) {
                    $u->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            });
        }

        $assignments = $query->latest()->paginate(15);

        $products = Product::where('status', 'available')->where('quantity', '>', 0)->get();
        $users = User::all();

        // Stats for the dashboard cards
        $stats = [
            'total' => Assignment::count(),
            'active' => Assignment::whereNull('actual_return_date')->count(),
            'overdue' => Assignment::whereNull('actual_return_date')
                            ->where('expected_return_date', '<', now())
                            ->count(),
            'returned' => Assignment::whereNotNull('actual_return_date')->count(),
            'returned_today' => Assignment::whereDate('actual_return_date', today())->count(),
        ];

        return view('admin.assignments.index', compact('assignments', 'products', 'users', 'stats'));
    }

    /**
     * Show the form for creating a new assignment.
     * Requires 'assignment.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('assignment.create')) {
            abort(403, 'You do not have permission to create assignments.');
        }

        $products = Product::where('status', 'available')->where('quantity', '>', 0)->get();
        $users = User::all();

        return view('admin.assignments.create', compact('products', 'users'));
    }

    /**
     * Store a newly created assignment.
     * Requires 'assignment.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('assignment.create')) {
            return response()->json(['error' => 'You do not have permission to create assignments.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after:assigned_date',
            'condition_out' => 'required|in:new,good,fair,poor',
            'notes' => 'nullable|string',
        ]);

        // Check if product is available
        $product = Product::findOrFail($validated['product_id']);

        if ($product->quantity <= 0) {
            return response()->json(['error' => 'Product is not available.'], 422);
        }

        // For software, check available licenses
        if ($product->isSoftware() && $product->available_licenses <= 0) {
            return response()->json(['error' => 'No available licenses for this software.'], 422);
        }

        $validated['assigned_by'] = auth()->id();
        $assignment = Assignment::create($validated);

        // Update product quantity
        if ($product->isHardware()) {
            $product->quantity -= 1;
            if ($product->quantity == 0) {
                $product->status = 'assigned';
            }
            $product->save();
        } else {
            // For software, decrement available licenses
            $product->available_licenses -= 1;
            $product->quantity -= 1;
            if ($product->available_licenses == 0) {
                $product->status = 'assigned';
            }
            $product->save();

            // Record license usage if license key is provided
            if ($request->assigned_license_key) {
                \App\Models\SoftwareLicenseUsage::create([
                    'product_id' => $product->id,
                    'user_id' => $validated['user_id'],
                    'license_key' => $request->assigned_license_key,
                    'assigned_at' => $validated['assigned_date'],
                ]);
            }
        }

        // Record stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'adjustment',
            'quantity_change' => -1,
            'previous_quantity' => $product->quantity + 1,
            'new_quantity' => $product->quantity,
            'reference' => 'ASSIGN-' . $assignment->id,
            'notes' => 'Asset assigned to user',
        ]);

        // Notify the user
        Notification::create([
            'user_id' => $validated['user_id'],
            'type' => 'assignment',
            'message' => "You have been assigned: {$product->name}",
            'link' => route('user.my-assignments'),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Assignment created successfully!', 'assignment' => $assignment]);
        }

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment created successfully!');
    }

    /**
     * Display the specified assignment.
     * Requires 'assignment.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('assignment.view')) {
            return response()->json(['error' => 'You do not have permission to view assignments.'], 403);
        }

        $assignment = Assignment::with(['product', 'user', 'assignedBy'])->findOrFail($id);
        return response()->json($assignment);
    }

     /**
     * Show the form for editing the specified assignment.
     * Requires 'assignment.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('assignment.edit')) {
            return response()->json(['error' => 'You do not have permission to edit assignments.'], 403);
        }

        $assignment = Assignment::with(['product', 'user'])->findOrFail($id);
        
        // Only allow editing active assignments
        if ($assignment->actual_return_date !== null) {
            return response()->json(['error' => 'Cannot edit returned assignments.'], 422);
        }

        // Return JSON data for the modal
        return response()->json([
            'id' => $assignment->id,
            'product_id' => $assignment->product_id,
            'user_id' => $assignment->user_id,
            'assigned_date' => $assignment->assigned_date ? $assignment->assigned_date->format('Y-m-d') : null,
            'expected_return_date' => $assignment->expected_return_date ? $assignment->expected_return_date->format('Y-m-d') : null,
            'condition_out' => $assignment->condition_out,
            'notes' => $assignment->notes,
        ]);
    }

    /**
     * Update the specified assignment.
     * Requires 'assignment.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('assignment.edit')) {
            return response()->json(['error' => 'You do not have permission to edit assignments.'], 403);
        }

        $assignment = Assignment::findOrFail($id);

        // Only allow editing active assignments
        if ($assignment->actual_return_date !== null) {
            return response()->json(['error' => 'Cannot edit returned assignments.'], 422);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after:assigned_date',
            'condition_out' => 'required|in:new,good,fair,poor',
            'notes' => 'nullable|string',
        ]);

        $assignment->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Assignment updated successfully!']);
        }

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment updated successfully!');
    }

    /**
     * Remove the specified assignment.
     * Requires 'assignment.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('assignment.delete')) {
            return response()->json(['error' => 'You do not have permission to delete assignments.'], 403);
        }

        $assignment = Assignment::findOrFail($id);

        if ($assignment->actual_return_date === null) {
            return response()->json(['error' => 'Cannot delete active assignment. Please return the asset first.'], 422);
        }

        $assignment->delete();

        return response()->json(['success' => true, 'message' => 'Assignment deleted successfully!']);
    }

    /**
     * Mark an assignment as returned.
     * Requires 'assignment.edit' permission.
     */
    public function returnAsset(Request $request, $id)
    {
        if (!auth()->user()->can('assignment.edit')) {
            return response()->json(['error' => 'You do not have permission to return assets.'], 403);
        }

        $assignment = Assignment::findOrFail($id);

        if ($assignment->actual_return_date !== null) {
            return response()->json(['error' => 'This asset has already been returned.'], 422);
        }

        $validated = $request->validate([
            'condition_in' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string',
        ]);

        $validated['actual_return_date'] = now();

        $assignment->update($validated);

        // Update product quantity
        $product = $assignment->product;
        
        if ($product->isHardware()) {
            $product->quantity += 1;
            if ($product->status === 'assigned' && $product->quantity > 0) {
                $product->status = 'available';
            }
            $product->save();
        } else {
            // For software, increment available licenses
            $product->available_licenses += 1;
            $product->quantity += 1;
            if ($product->status === 'assigned' && $product->available_licenses > 0) {
                $product->status = 'available';
            }
            $product->save();

            // Revoke license usage if exists
            $licenseUsage = \App\Models\SoftwareLicenseUsage::where('product_id', $product->id)
                ->where('user_id', $assignment->user_id)
                ->whereNull('revoked_at')
                ->first();
            
            if ($licenseUsage) {
                $licenseUsage->update([
                    'revoked_at' => now(),
                ]);
            }
        }

        // Record stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'return',
            'quantity_change' => 1,
            'previous_quantity' => $product->quantity - 1,
            'new_quantity' => $product->quantity,
            'reference' => 'RETURN-' . $assignment->id,
            'notes' => 'Asset returned by user',
        ]);

        // Notify the user
        Notification::create([
            'user_id' => $assignment->user_id,
            'type' => 'assignment',
            'message' => "Asset returned: {$product->name}",
            'link' => route('user.my-assignments'),
        ]);

        return response()->json(['success' => true, 'message' => 'Asset returned successfully!']);
    }

    /**
     * Bulk return multiple assignments.
     * Requires 'assignment.edit' permission.
     */
    public function bulkReturn(Request $request)
    {
        if (!auth()->user()->can('assignment.edit')) {
            return response()->json(['error' => 'You do not have permission to bulk return assets.'], 403);
        }

        $request->validate([
            'assignment_ids' => 'required|array|min:1',
            'assignment_ids.*' => 'exists:assignments,id',
            'condition_in' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string',
        ]);

        $assignments = Assignment::whereIn('id', $request->assignment_ids)
            ->whereNull('actual_return_date')
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['error' => 'No active assignments found to return.'], 422);
        }

        foreach ($assignments as $assignment) {
            $assignment->update([
                'actual_return_date' => now(),
                'condition_in' => $request->condition_in,
                'notes' => $request->notes ? $request->notes . ' (Bulk return)' : 'Bulk return',
            ]);

            // Update product quantity
            $product = $assignment->product;
            
            if ($product->isHardware()) {
                $product->quantity += 1;
                if ($product->status === 'assigned' && $product->quantity > 0) {
                    $product->status = 'available';
                }
                $product->save();
            } else {
                $product->available_licenses += 1;
                $product->quantity += 1;
                if ($product->status === 'assigned' && $product->available_licenses > 0) {
                    $product->status = 'available';
                }
                $product->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($assignments) . ' assets returned successfully!'
        ]);
    }

    /**
     * Extend an assignment's return date.
     * Requires 'assignment.edit' permission.
     */
    public function extend(Request $request, $id)
    {
        if (!auth()->user()->can('assignment.edit')) {
            return response()->json(['error' => 'You do not have permission to extend assignments.'], 403);
        }

        $request->validate([
            'new_return_date' => 'required|date|after:today',
            'reason' => 'nullable|string|max:255',
        ]);

        $assignment = Assignment::findOrFail($id);

        if ($assignment->actual_return_date !== null) {
            return response()->json(['error' => 'Cannot extend a returned assignment.'], 422);
        }

        $oldDate = $assignment->expected_return_date;
        $assignment->update([
            'expected_return_date' => $request->new_return_date,
            'notes' => ($assignment->notes ? $assignment->notes . "\n" : '') . 
                       'Extension: ' . ($request->reason ?? 'No reason provided') . 
                       ' (Old date: ' . ($oldDate ? $oldDate->format('Y-m-d') : 'Not set') . 
                       ', New date: ' . $request->new_return_date . ')',
        ]);

        // Notify user
        Notification::create([
            'user_id' => $assignment->user_id,
            'type' => 'assignment',
            'message' => "Your assignment for {$assignment->product->name} has been extended to " . 
                         date('M d, Y', strtotime($request->new_return_date)),
            'link' => route('user.my-assignments'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment extended successfully!',
                'new_return_date' => $request->new_return_date,
            ]);
        }

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment extended successfully!');
    }

    /**
     * Get assignments for the current user.
     */
    public function myAssignments()
    {
        $userId = auth()->id();
        
        $activeAssignments = Assignment::with('product')
            ->where('user_id', $userId)
            ->whereNull('actual_return_date')
            ->latest()
            ->get();

        $returnedAssignments = Assignment::with('product')
            ->where('user_id', $userId)
            ->whereNotNull('actual_return_date')
            ->latest()
            ->paginate(10);

        return view('user.assignments.index', compact('activeAssignments', 'returnedAssignments'));
    }

    /**
     * Get available products for assignment.
     */
    public function availableProducts()
    {
        if (!auth()->user()->can('assignment.create')) {
            return response()->json(['error' => 'You do not have permission to view available products.'], 403);
        }

        $products = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->with('category')
            ->get();

        return response()->json($products);
    }

    /**
     * Get active assignments for a user.
     */
    public function getUserAssignments($userId)
    {
        if (!auth()->user()->can('assignment.view')) {
            return response()->json(['error' => 'You do not have permission to view assignments.'], 403);
        }

        $assignments = Assignment::with('product')
            ->where('user_id', $userId)
            ->whereNull('actual_return_date')
            ->get();

        return response()->json($assignments);
    }

    /**
     * Get assignment history for a product.
     */
    public function getProductHistory($productId)
    {
        if (!auth()->user()->can('assignment.view')) {
            return response()->json(['error' => 'You do not have permission to view assignments.'], 403);
        }

        $assignments = Assignment::with('user')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($assignments);
    }

    /**
     * Export assignments to CSV/Excel.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('assignment.view')) {
            return response()->json(['error' => 'You do not have permission to export assignments.'], 403);
        }

        $assignments = Assignment::with(['product', 'user', 'assignedBy'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $assignments,
            'count' => $assignments->count(),
            'exported_at' => now(),
        ]);
    }

    /**
     * Generate assignment report.
     */
    public function report(Request $request)
    {
        if (!auth()->user()->can('assignment.view')) {
            return response()->json(['error' => 'You do not have permission to view reports.'], 403);
        }

        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now();

        $report = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'total_assignments' => Assignment::whereBetween('created_at', [$startDate, $endDate])->count(),
                'active_assignments' => Assignment::whereNull('actual_return_date')->count(),
                'returned_assignments' => Assignment::whereNotNull('actual_return_date')
                    ->whereBetween('actual_return_date', [$startDate, $endDate])
                    ->count(),
                'overdue_assignments' => Assignment::whereNull('actual_return_date')
                    ->where('expected_return_date', '<', now())
                    ->count(),
            ],
            'by_user' => Assignment::with('user')
                ->select('user_id', \DB::raw('count(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('user_id')
                ->get(),
            'by_product' => Assignment::with('product')
                ->select('product_id', \DB::raw('count(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('product_id')
                ->get(),
            'by_condition' => [
                'new' => Assignment::where('condition_out', 'new')->count(),
                'good' => Assignment::where('condition_out', 'good')->count(),
                'fair' => Assignment::where('condition_out', 'fair')->count(),
                'poor' => Assignment::where('condition_out', 'poor')->count(),
            ],
        ];

        return response()->json($report);
    }

    /**
     * Get assignment statistics.
     */
    public function statistics()
    {
        if (!auth()->user()->can('assignment.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => Assignment::count(),
            'active' => Assignment::whereNull('actual_return_date')->count(),
            'returned' => Assignment::whereNotNull('actual_return_date')->count(),
            'overdue' => Assignment::whereNull('actual_return_date')
                ->where('expected_return_date', '<', now())
                ->count(),
            'by_condition_out' => Assignment::select('condition_out', \DB::raw('count(*) as total'))
                ->groupBy('condition_out')
                ->pluck('total', 'condition_out'),
            'by_condition_in' => Assignment::select('condition_in', \DB::raw('count(*) as total'))
                ->whereNotNull('condition_in')
                ->groupBy('condition_in')
                ->pluck('total', 'condition_in'),
            'by_month' => Assignment::select(
                \DB::raw('YEAR(created_at) as year'),
                \DB::raw('MONTH(created_at) as month'),
                \DB::raw('count(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get(),
            'top_products' => Assignment::with('product')
                ->select('product_id', \DB::raw('count(*) as total'))
                ->groupBy('product_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }
}
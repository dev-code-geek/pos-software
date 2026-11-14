<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    /**
     * Display a listing of user's assignments.
     */
    public function index(Request $request)
    {
        try {
            $query = Assignment::where('user_id', auth()->id())
                ->with(['product', 'assignedBy']);

            // Filter by status
            if ($request->has('status')) {
                switch ($request->status) {
                    case 'active':
                        $query->whereNull('actual_return_date');
                        break;
                    case 'returned':
                        $query->whereNotNull('actual_return_date');
                        break;
                    case 'overdue':
                        $query->whereNull('actual_return_date')
                              ->where('expected_return_date', '<', now());
                        break;
                }
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('product', function($p) use ($search) {
                        $p->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('sku', 'LIKE', "%{$search}%");
                    });
                });
            }

            $assignments = $query->latest('assigned_date')->paginate(15);

            // Stats
            $stats = [
                'active' => Assignment::where('user_id', auth()->id())
                    ->whereNull('actual_return_date')
                    ->count(),
                'overdue' => Assignment::where('user_id', auth()->id())
                    ->whereNull('actual_return_date')
                    ->where('expected_return_date', '<', now())
                    ->count(),
                'returned' => Assignment::where('user_id', auth()->id())
                    ->whereNotNull('actual_return_date')
                    ->count(),
                'total' => Assignment::where('user_id', auth()->id())->count(),
            ];

            return view('user.assignments.index', compact('assignments', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error loading assignments: ' . $e->getMessage());
            return back()->with('error', 'Failed to load assignments. Please try again.');
        }
    }

    /**
     * Display assignment history.
     */
    public function history(Request $request)
    {
        try {
            $query = Assignment::where('user_id', auth()->id())
                ->whereNotNull('actual_return_date')
                ->with(['product']);

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('product', function($p) use ($search) {
                    $p->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            $assignments = $query->latest('actual_return_date')->paginate(15);

            return view('user.assignments.history', compact('assignments'));
        } catch (\Exception $e) {
            Log::error('Error loading assignment history: ' . $e->getMessage());
            return back()->with('error', 'Failed to load assignment history.');
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show($id)
    {
        try {
            $assignment = Assignment::where('user_id', auth()->id())
                ->with(['product', 'assignedBy', 'product.category'])
                ->findOrFail($id);

            return view('user.assignments.show', compact('assignment'));
        } catch (\Exception $e) {
            Log::error('Error loading assignment details: ' . $e->getMessage());
            return redirect()
                ->route('user.assignments.index')
                ->with('error', 'Assignment not found.');
        }
    }

    /**
     * Return an assigned asset.
     */
    public function returnAsset(Request $request, $id)
    {
        try {
            $assignment = Assignment::where('user_id', auth()->id())
                ->where('id', $id)
                ->whereNull('actual_return_date')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'error' => 'Assignment not found or already returned.'
                ], 404);
            }

            $validated = $request->validate([
                'condition_in' => 'required|in:good,fair,poor,damaged',
                'notes' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            // Update assignment
            $assignment->update([
                'actual_return_date' => now(),
                'condition_in' => $validated['condition_in'],
                'notes' => $validated['notes'] ?? $assignment->notes,
            ]);

            // Update product quantity
            $product = $assignment->product;
            $oldQuantity = $product->quantity;
            $product->quantity += 1;
            
            if ($product->status === 'assigned' && $product->quantity > 0) {
                $product->status = 'available';
            }
            $product->save();

            // Build notes safely
            $notes = sprintf(
                "Asset returned by user: %s. Condition: %s. Notes: %s",
                auth()->user()->name,
                $validated['condition_in'],
                $validated['notes'] ?? 'No notes provided'
            );

            // Record stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'return',
                'quantity_change' => 1,
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $product->quantity,
                'reference' => 'RETURN-USER-' . $assignment->id,
                'notes' => $notes,
            ]);

            // Notify admins
            $admins = User::role(['admin', 'ict_officer', 'super_admin'])->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'assignment',
                    'message' => "Asset returned by " . auth()->user()->name . ": {$product->name} (SKU: {$product->sku})",
                    'link' => route('admin.assignments.show', $assignment->id),
                ]);
            }

            // Notify the user
            Notification::create([
                'user_id' => auth()->id(),
                'type' => 'assignment',
                'message' => "You have successfully returned: {$product->name}",
                'link' => route('user.assignments.show', $assignment->id),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset returned successfully!',
                'assignment' => $assignment
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error returning asset: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to return asset. Please try again.'
            ], 500);
        }
    }

    /**
     * Extend the return date of an assignment.
     */
    public function extendReturnDate(Request $request, $id)
    {
        try {
            $assignment = Assignment::where('user_id', auth()->id())
                ->where('id', $id)
                ->whereNull('actual_return_date')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'error' => 'Assignment not found or already returned.'
                ], 404);
            }

            $validated = $request->validate([
                'new_return_date' => 'required|date|after:' . now()->addDay()->format('Y-m-d'),
                'reason' => 'nullable|string|max:255',
            ]);

            $oldDate = $assignment->expected_return_date;
            
            $assignment->update([
                'expected_return_date' => $validated['new_return_date'],
                'notes' => ($assignment->notes ? $assignment->notes . "\n" : '') . 
                           "Extension requested by user: " . auth()->user()->name . 
                           " (Old date: " . ($oldDate ? $oldDate->format('Y-m-d') : 'Not set') . 
                           ", New date: " . $validated['new_return_date'] . 
                           ", Reason: " . ($validated['reason'] ?? 'No reason provided') . ")",
            ]);

            // Notify admins about extension request
            $admins = User::role(['admin', 'ict_officer', 'super_admin'])->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'assignment',
                    'message' => "Extension requested by " . auth()->user()->name . " for: {$assignment->product->name}",
                    'link' => route('admin.assignments.show', $assignment->id),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Return date extended successfully!',
                'new_date' => $validated['new_return_date']
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error extending return date: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to extend return date. Please try again.'
            ], 500);
        }
    }

    /**
     * Get assignment statistics for the user.
     */
    public function getStats()
    {
        try {
            $userId = auth()->id();

            $stats = [
                'total' => Assignment::where('user_id', $userId)->count(),
                'active' => Assignment::where('user_id', $userId)
                    ->whereNull('actual_return_date')
                    ->count(),
                'overdue' => Assignment::where('user_id', $userId)
                    ->whereNull('actual_return_date')
                    ->where('expected_return_date', '<', now())
                    ->count(),
                'returned' => Assignment::where('user_id', $userId)
                    ->whereNotNull('actual_return_date')
                    ->count(),
                'returned_this_month' => Assignment::where('user_id', $userId)
                    ->whereNotNull('actual_return_date')
                    ->whereMonth('actual_return_date', now()->month)
                    ->whereYear('actual_return_date', now()->year)
                    ->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting assignment stats: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load statistics.'
            ], 500);
        }
    }

    /**
     * Get upcoming returns for the user.
     */
    public function upcomingReturns()
    {
        try {
            $assignments = Assignment::where('user_id', auth()->id())
                ->whereNull('actual_return_date')
                ->where('expected_return_date', '<=', now()->addDays(7))
                ->where('expected_return_date', '>', now())
                ->with(['product'])
                ->orderBy('expected_return_date', 'asc')
                ->get();

            return response()->json([
                'upcoming' => $assignments,
                'count' => $assignments->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading upcoming returns: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load upcoming returns.'
            ], 500);
        }
    }

    /**
     * Download assignment report (PDF/Excel).
     */
    public function downloadReport(Request $request)
    {
        try {
            $assignments = Assignment::where('user_id', auth()->id())
                ->with(['product'])
                ->latest()
                ->get();

            // Generate CSV data
            $csvData = [];
            $csvData[] = ['#', 'Asset Name', 'SKU', 'Assigned Date', 'Expected Return', 'Actual Return', 'Status', 'Condition In'];

            foreach ($assignments as $index => $assignment) {
                $status = 'Active';
                if ($assignment->actual_return_date) {
                    $status = 'Returned';
                } elseif ($assignment->expected_return_date && $assignment->expected_return_date < now()) {
                    $status = 'Overdue';
                }

                $csvData[] = [
                    $index + 1,
                    $assignment->product->name ?? 'N/A',
                    $assignment->product->sku ?? 'N/A',
                    $assignment->assigned_date ? $assignment->assigned_date->format('Y-m-d') : 'N/A',
                    $assignment->expected_return_date ? $assignment->expected_return_date->format('Y-m-d') : 'N/A',
                    $assignment->actual_return_date ? $assignment->actual_return_date->format('Y-m-d') : 'N/A',
                    $status,
                    $assignment->condition_in ?? 'N/A',
                ];
            }

            // For now, return JSON. You can integrate with Excel package later.
            return response()->json([
                'data' => $csvData,
                'count' => count($csvData) - 1,
                'exported_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error downloading assignment report: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to download report.'
            ], 500);
        }
    }

    /**
     * Get dashboard widget data for user sidebar.
     */
    public function widget()
    {
        try {
            $userId = auth()->id();

            $stats = [
                'active_assignments' => Assignment::where('user_id', $userId)
                    ->whereNull('actual_return_date')
                    ->count(),
                'overdue_assignments' => Assignment::where('user_id', $userId)
                    ->whereNull('actual_return_date')
                    ->where('expected_return_date', '<', now())
                    ->count(),
                'upcoming_returns' => Assignment::where('user_id', $userId)
                    ->whereNull('actual_return_date')
                    ->where('expected_return_date', '<=', now()->addDays(7))
                    ->where('expected_return_date', '>', now())
                    ->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error loading widget data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load widget data.'
            ], 500);
        }
    }
}
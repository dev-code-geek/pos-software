<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\RequestWorkflow;
use App\Models\Assignment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AssetRequestController extends Controller
{
    /**
     * Display a listing of asset requests.
     */
    public function index()
    {
        $requests = AssetRequest::with(['user', 'category', 'assignedProduct'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => AssetRequest::count(),
            'pending' => AssetRequest::whereIn('status', ['pending_ict_review', 'ict_reviewing'])->count(),
            'approved' => AssetRequest::where('status', 'approved_by_ict')->count(),
            'assigned' => AssetRequest::where('status', 'assigned_to_user')->count(),
            'rejected' => AssetRequest::where('status', 'rejected_by_ict')->count(),
            'in_procurement' => AssetRequest::whereIn('status', [
                'sent_to_procurement', 'procurement_reviewing', 'ready_for_purchase', 
                'ordered', 'received', 'under_inspection'
            ])->count(),
        ];

        $categories = Category::all();
        $statusCounts = AssetRequest::select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.asset-requests.index', compact('requests', 'stats', 'categories', 'statusCounts'));
    }

    /**
     * Show the form for creating a new asset request.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.asset-requests.create', compact('categories'));
    }

    /**
     * Store a newly created asset request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'request_type' => 'required|in:new,replacement,upgrade',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'specifications' => 'nullable|string',
            'quantity_requested' => 'required|integer|min:1',
            'priority' => 'required|in:low,medium,high,urgent',
            'justification' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending_ict_review';

        $assetRequest = AssetRequest::create($validated);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'ict',
            'action' => 'submitted',
            'status_after' => 'pending_ict_review',
            'comments' => 'Request submitted by admin',
        ]);

        // Notify ICT staff
        $ictStaff = User::role('ict_officer')->get();
        foreach ($ictStaff as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'assignment',
                'message' => 'New asset request #' . $assetRequest->id . ' submitted by ' . auth()->user()->name,
                'link' => route('admin.asset-requests.show', $assetRequest->id),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Request submitted successfully!',
            'redirect' => route('admin.asset-requests.index'),
        ]);
    }

     /**
     * Display the specified asset request.
     * Requires 'asset_request.view' permission.
     */
    public function show($id)
    {
        try {
            \Log::info('Show asset request called for ID: ' . $id);
            
            if (!auth()->user()->can('asset_request.view')) {
                \Log::warning('User does not have view permission: ' . auth()->user()->id);
                return response()->json(['error' => 'You do not have permission to view asset requests.'], 403);
            }

            $assetRequest = AssetRequest::find($id);
            
            if (!$assetRequest) {
                \Log::warning('Asset request not found: ' . $id);
                return response()->json(['error' => 'Asset request not found'], 404);
            }

            \Log::info('Asset request found: ' . $assetRequest->id);

            // Load relationships with error handling
            try {
                $assetRequest->load([
                    'user' => function($query) {
                        $query->select('id', 'name', 'email');
                    },
                    'category' => function($query) {
                        $query->select('id', 'name');
                    },
                    'assignedProduct' => function($query) {
                        $query->select('id', 'name', 'sku', 'serial_number');
                    },
                    'workflows' => function($query) {
                        $query->with('actor')->orderBy('created_at', 'asc');
                    }
                ]);
                \Log::info('Relationships loaded successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to load relationships: ' . $e->getMessage());
                // Continue without relationships if they fail
            }

            // Format the response data
            $responseData = [
                'id' => $assetRequest->id,
                'user_id' => $assetRequest->user_id,
                'user' => $assetRequest->user ? [
                    'id' => $assetRequest->user->id,
                    'name' => $assetRequest->user->name,
                    'email' => $assetRequest->user->email,
                ] : null,
                'category_id' => $assetRequest->category_id,
                'category' => $assetRequest->category ? [
                    'id' => $assetRequest->category->id,
                    'name' => $assetRequest->category->name,
                ] : null,
                'assigned_product_id' => $assetRequest->assigned_product_id,
                'assigned_product' => $assetRequest->assignedProduct ? [
                    'id' => $assetRequest->assignedProduct->id,
                    'name' => $assetRequest->assignedProduct->name,
                    'sku' => $assetRequest->assignedProduct->sku,
                    'serial_number' => $assetRequest->assignedProduct->serial_number,
                ] : null,
                'title' => $assetRequest->title,
                'description' => $assetRequest->description,
                'specifications' => $assetRequest->specifications,
                'request_type' => $assetRequest->request_type,
                'quantity_requested' => $assetRequest->quantity_requested,
                'priority' => $assetRequest->priority,
                'justification' => $assetRequest->justification,
                'ict_notes' => $assetRequest->ict_notes,
                'director_notes' => $assetRequest->director_notes,
                'procurement_notes' => $assetRequest->procurement_notes,
                'rejection_reason' => $assetRequest->rejection_reason,
                'status' => $assetRequest->status,
                'created_at' => $assetRequest->created_at,
                'updated_at' => $assetRequest->updated_at,
                'workflows' => $assetRequest->workflows ? $assetRequest->workflows->map(function($workflow) {
                    return [
                        'id' => $workflow->id,
                        'action' => $workflow->action,
                        'comments' => $workflow->comments,
                        'status_before' => $workflow->status_before,
                        'status_after' => $workflow->status_after,
                        'created_at' => $workflow->created_at,
                        'actor' => $workflow->actor ? [
                            'id' => $workflow->actor->id,
                            'name' => $workflow->actor->name,
                        ] : null,
                    ];
                }) : [],
            ];

            return response()->json($responseData);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Model not found: ' . $e->getMessage());
            return response()->json(['error' => 'Asset request not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Show error: ' . $e->getMessage());
            \Log::error('Show error trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to load asset request details',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while loading the request.',
                'file' => config('app.debug') ? $e->getFile() : null,
                'line' => config('app.debug') ? $e->getLine() : null
            ], 500);
        }
    }
     /**
     * Show the form for editing the specified asset request.
     * Requires 'asset_request.edit' permission.
     */
    public function edit($id)
    {
        try {
            // Log the request
            \Log::info('Edit asset request called for ID: ' . $id);
            
            // Check permission
            if (!auth()->user()->can('asset_request.edit')) {
                \Log::warning('User does not have edit permission: ' . auth()->user()->id);
                abort(403, 'You do not have permission to edit asset requests.');
            }

            // Find the asset request
            $assetRequest = AssetRequest::find($id);
            
            if (!$assetRequest) {
                \Log::warning('Asset request not found: ' . $id);
                return response()->json(['error' => 'Asset request not found'], 404);
            }

            \Log::info('Asset request found: ' . $assetRequest->id);

            // Load relationships
            try {
                $assetRequest->load(['user', 'category']);
                \Log::info('Relationships loaded successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to load relationships: ' . $e->getMessage());
                // Continue without relationships if they fail
            }

            // Return the asset request as JSON
            return response()->json($assetRequest);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Model not found: ' . $e->getMessage());
            return response()->json(['error' => 'Asset request not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Edit error: ' . $e->getMessage());
            \Log::error('Edit error trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to load request data',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Update the specified asset request.
     */
    public function update(Request $request, $id)
    {
        $assetRequest = AssetRequest::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'request_type' => 'required|in:new,replacement,upgrade',
            'category_id' => 'nullable|exists:categories,id',
            'quantity_requested' => 'required|integer|min:1',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string',
            'specifications' => 'nullable|string',
            'justification' => 'nullable|string',
            'ict_notes' => 'nullable|string',
        ]);

        $assetRequest->update($validated);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'ict',
            'action' => 'updated',
            'status_before' => $assetRequest->status,
            'status_after' => $assetRequest->status,
            'comments' => 'Request details updated',
        ]);

        return response()->json(['success' => true, 'message' => 'Request updated successfully!']);
    }

    /**
     * Remove the specified asset request.
     */
    public function destroy($id)
    {
        $assetRequest = AssetRequest::findOrFail($id);
        
        // Check if request can be deleted (only if pending)
        if (!in_array($assetRequest->status, ['pending_ict_review', 'ict_reviewing', 'rejected_by_ict'])) {
            return response()->json([
                'error' => 'Cannot delete request that is already in progress'
            ], 422);
        }
        
        $assetRequest->delete();

        return response()->json(['success' => true, 'message' => 'Request deleted successfully!']);
    }

    /**
     * Review and approve the asset request.
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'action' => 'required|in:approve,reject,forward_director,assign_existing',
        ]);

        $assetRequest = AssetRequest::findOrFail($id);

        // Update request status based on action
        $statusMap = [
            'approve' => 'approved_by_ict',
            'reject' => 'rejected_by_ict',
            'forward_director' => 'sent_to_director',
            'assign_existing' => 'assigned_to_user',
        ];

        $newStatus = $statusMap[$request->action] ?? $assetRequest->status;

        $assetRequest->update([
            'status' => $newStatus,
            'ict_notes' => $request->notes,
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => $request->action === 'forward_director' ? 'director' : 'user',
            'action' => $request->action,
            'status_before' => $assetRequest->status,
            'status_after' => $newStatus,
            'comments' => $request->notes ?? 'ICT reviewed request',
        ]);

        // Notify user
        Notification::create([
            'user_id' => $assetRequest->user_id,
            'type' => 'assignment',
            'message' => 'Your asset request #' . $assetRequest->id . ' has been ' . str_replace('_', ' ', $newStatus),
            'link' => route('user.asset-requests.show', $assetRequest->id),
        ]);

        return response()->json(['success' => true, 'message' => 'Request ' . str_replace('_', ' ', $newStatus) . ' successfully!']);
    }

    /**
     * Reject the asset request.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $assetRequest = AssetRequest::findOrFail($id);

        $oldStatus = $assetRequest->status;
        $assetRequest->update([
            'status' => 'rejected_by_ict',
            'rejection_reason' => $request->reason,
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'user',
            'action' => 'rejected',
            'status_before' => $oldStatus,
            'status_after' => 'rejected_by_ict',
            'comments' => $request->reason,
        ]);

        // Notify user
        Notification::create([
            'user_id' => $assetRequest->user_id,
            'type' => 'assignment',
            'message' => 'Your asset request #' . $assetRequest->id . ' has been rejected. Reason: ' . $request->reason,
            'link' => route('user.asset-requests.show', $assetRequest->id),
        ]);

        return response()->json(['success' => true, 'message' => 'Request rejected successfully!']);
    }

    /**
     * Forward request to Director.
     */
    public function forwardToDirector(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $assetRequest = AssetRequest::findOrFail($id);

        $oldStatus = $assetRequest->status;
        $assetRequest->update([
            'status' => 'sent_to_director',
            'ict_notes' => $request->notes,
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'director',
            'action' => 'forwarded_to_director',
            'status_before' => $oldStatus,
            'status_after' => 'sent_to_director',
            'comments' => $request->notes ?? 'Forwarded to Director for approval',
        ]);

        // Notify Director
        $directors = User::role('director')->get();
        foreach ($directors as $director) {
            Notification::create([
                'user_id' => $director->id,
                'type' => 'assignment',
                'message' => 'Asset request #' . $assetRequest->id . ' needs your review',
                'link' => route('admin.asset-requests.show', $assetRequest->id),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Request forwarded to Director successfully!']);
    }

    /**
     * Forward request to Procurement.
     */
    public function forwardToProcurement(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $assetRequest = AssetRequest::findOrFail($id);

        $oldStatus = $assetRequest->status;
        $assetRequest->update([
            'status' => 'sent_to_procurement',
            'director_notes' => $request->notes,
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'director',
            'to_department' => 'procurement',
            'action' => 'forwarded_to_procurement',
            'status_before' => $oldStatus,
            'status_after' => 'sent_to_procurement',
            'comments' => $request->notes ?? 'Forwarded to Procurement for processing',
        ]);

        // Notify Procurement staff
        $procurementStaff = User::role('procurement_officer')->get();
        foreach ($procurementStaff as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'assignment',
                'message' => 'Asset request #' . $assetRequest->id . ' needs procurement processing',
                'link' => route('admin.asset-requests.show', $assetRequest->id),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Request forwarded to Procurement successfully!']);
    }

    /**
     * Assign an existing product to fulfill the request.
     */
    public function assignExisting(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'notes' => 'nullable|string',
        ]);

        $assetRequest = AssetRequest::findOrFail($id);
        $product = Product::findOrFail($request->product_id);

        if ($product->quantity <= 0 || $product->status !== 'available') {
            return response()->json(['error' => 'Product is not available'], 422);
        }

        // Deduct stock
        $product->quantity -= 1;
        if ($product->quantity == 0) {
            $product->status = 'assigned';
        }
        $product->save();

        // Create assignment
        Assignment::create([
            'product_id' => $product->id,
            'user_id' => $assetRequest->user_id,
            'assigned_by' => auth()->id(),
            'assigned_date' => now(),
            'condition_out' => 'good',
            'notes' => $request->notes ?? 'Assigned from request #' . $assetRequest->id,
        ]);

        // Update request
        $oldStatus = $assetRequest->status;
        $assetRequest->update([
            'status' => 'assigned_to_user',
            'assigned_product_id' => $product->id,
            'ict_notes' => $request->notes,
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'user',
            'action' => 'assigned_existing',
            'status_before' => $oldStatus,
            'status_after' => 'assigned_to_user',
            'comments' => $request->notes ?? 'Asset assigned to user',
        ]);

        // Notify user
        Notification::create([
            'user_id' => $assetRequest->user_id,
            'type' => 'assignment',
            'message' => 'Asset "' . $product->name . '" has been assigned to you.',
            'link' => route('user.my-assignments'),
        ]);

        return response()->json(['success' => true, 'message' => 'Asset assigned successfully!']);
    }

    /**
     * Get available products for assignment.
     */
    public function availableProducts($id)
    {
        $assetRequest = AssetRequest::findOrFail($id);
        $products = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->get(['id', 'name', 'sku', 'quantity', 'category_id']);

        return response()->json([
            'products' => $products,
            'user_name' => $assetRequest->user->name,
            'category_name' => $assetRequest->category ? $assetRequest->category->name : null,
        ]);
    }

    /**
     * Get request timeline.
     */
    public function timeline($id)
    {
        $workflows = RequestWorkflow::where('asset_request_id', $id)
            ->with('actor')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($workflows);
    }

    /**
     * Update specification for the request.
     */
    public function specify(Request $request, $id)
    {
        $request->validate([
            'specifications' => 'required|string|min:10',
            'notes' => 'nullable|string',
        ]);

        $assetRequest = AssetRequest::findOrFail($id);

        $oldSpecs = $assetRequest->specifications;
        $assetRequest->update([
            'specifications' => $request->specifications,
            'ict_notes' => $request->notes,
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'procurement',
            'action' => 'specified',
            'status_before' => $assetRequest->status,
            'status_after' => $assetRequest->status,
            'comments' => 'Specifications updated: ' . ($request->notes ?? ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Specifications updated successfully!',
            'specifications' => $request->specifications,
        ]);
    }

    /**
     * Mark request as completed.
     */
    public function complete($id)
    {
        $assetRequest = AssetRequest::findOrFail($id);

        $oldStatus = $assetRequest->status;
        $assetRequest->update([
            'status' => 'completed',
        ]);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'user',
            'action' => 'completed',
            'status_before' => $oldStatus,
            'status_after' => 'completed',
            'comments' => 'Request completed',
        ]);

        return response()->json(['success' => true, 'message' => 'Request marked as completed!']);
    }

    /**
     * Export asset requests.
     */
    public function export(Request $request)
    {
        $requests = AssetRequest::with(['user', 'category', 'assignedProduct'])
            ->latest()
            ->get();

        // You can implement CSV/Excel export here
        // For now, return a JSON response
        return response()->json([
            'data' => $requests,
            'count' => $requests->count(),
        ]);
    }

    /**
     * Bulk action on requests.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'exists:asset_requests,id',
            'action' => 'required|in:approve,reject,delete',
        ]);

        $ids = $request->request_ids;
        $action = $request->action;

        switch ($action) {
            case 'approve':
                AssetRequest::whereIn('id', $ids)
                    ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
                    ->update(['status' => 'approved_by_ict']);
                
                // Create workflows for each
                foreach ($ids as $id) {
                    RequestWorkflow::create([
                        'asset_request_id' => $id,
                        'actor_id' => auth()->id(),
                        'from_department' => 'ict',
                        'to_department' => 'user',
                        'action' => 'bulk_approved',
                        'status_after' => 'approved_by_ict',
                        'comments' => 'Bulk approval',
                    ]);
                }
                
                $message = 'Selected requests approved successfully!';
                break;
                
            case 'reject':
                AssetRequest::whereIn('id', $ids)
                    ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
                    ->update(['status' => 'rejected_by_ict']);
                $message = 'Selected requests rejected successfully!';
                break;
                
            case 'delete':
                AssetRequest::whereIn('id', $ids)
                    ->whereIn('status', ['pending_ict_review', 'ict_reviewing', 'rejected_by_ict'])
                    ->delete();
                $message = 'Selected requests deleted successfully!';
                break;
                
            default:
                return response()->json(['error' => 'Invalid action'], 422);
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Get request statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => AssetRequest::count(),
            'pending' => AssetRequest::whereIn('status', ['pending_ict_review', 'ict_reviewing'])->count(),
            'approved' => AssetRequest::where('status', 'approved_by_ict')->count(),
            'rejected' => AssetRequest::where('status', 'rejected_by_ict')->count(),
            'assigned' => AssetRequest::where('status', 'assigned_to_user')->count(),
            'completed' => AssetRequest::where('status', 'completed')->count(),
            'in_procurement' => AssetRequest::whereIn('status', [
                'sent_to_procurement', 'procurement_reviewing', 'ready_for_purchase', 
                'ordered', 'received', 'under_inspection'
            ])->count(),
            'by_priority' => AssetRequest::select('priority', \DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->pluck('total', 'priority'),
            'by_type' => AssetRequest::select('request_type', \DB::raw('count(*) as total'))
                ->groupBy('request_type')
                ->pluck('total', 'request_type'),
            'by_category' => AssetRequest::select('categories.name', \DB::raw('count(asset_requests.id) as total'))
                ->join('categories', 'asset_requests.category_id', '=', 'categories.id')
                ->groupBy('categories.name')
                ->pluck('total', 'categories.name'),
        ];

        return response()->json($stats);
    }
      /**
     * Cancel the specified asset request.
        */
        public function cancel($id)
        {
            $assetRequest = AssetRequest::findOrFail($id);
            
            // Check if user owns the request
            if (auth()->id() !== $assetRequest->user_id) {
                return response()->json(['error' => 'You cannot cancel this request'], 403);
            }
            
            // Check if request can be cancelled
            if (!in_array($assetRequest->status, ['pending_ict_review', 'ict_reviewing'])) {
                return response()->json(['error' => 'This request cannot be cancelled'], 422);
            }
            
            $assetRequest->update(['status' => 'cancelled']);
            
            RequestWorkflow::create([
                'asset_request_id' => $assetRequest->id,
                'actor_id' => auth()->id(),
                'from_department' => 'user',
                'to_department' => 'ict',
                'action' => 'cancelled',
                'status_before' => $assetRequest->status,
                'status_after' => 'cancelled',
                'comments' => 'Request cancelled by user',
            ]);
            
            return response()->json(['success' => true, 'message' => 'Request cancelled successfully!']);
        }
}
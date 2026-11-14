<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectRequest;
use App\Models\AssetRequest;
use App\Models\User;
use App\Models\RequestWorkflow;
use App\Models\Notification;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DirectRequestController extends Controller
{
    /**
     * Display a listing of direct requests.
     * Requires 'direct_request.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('direct_request.view')) {
            abort(403, 'You do not have permission to view direct requests.');
        }

        $query = DirectRequest::with(['user', 'director', 'assetRequest', 'category']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by urgency
        if ($request->has('urgency') && $request->urgency) {
            $query->where('urgency', $request->urgency);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by has approver
        if ($request->has('has_approver')) {
            if ($request->has_approver === 'yes') {
                $query->whereNotNull('director_id');
            } elseif ($request->has_approver === 'no') {
                $query->whereNull('director_id');
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('director', function($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total' => DirectRequest::count(),
            'pending_director' => DirectRequest::where('status', 'pending_director')->count(),
            'director_approved' => DirectRequest::where('status', 'director_approved')->count(),
            'director_rejected' => DirectRequest::where('status', 'director_rejected')->count(),
            'sent_to_ict' => DirectRequest::where('status', 'sent_to_ict')->count(),
            'ict_specified' => DirectRequest::where('status', 'ict_specified')->count(),
            'director_sent_to_procurement' => DirectRequest::where('status', 'director_sent_to_procurement')->count(),
            'completed' => DirectRequest::where('status', 'completed')->count(),
            'merged' => DirectRequest::where('status', 'merged_to_request')->count(),
            'without_approver' => DirectRequest::whereNull('director_id')->count(),
        ];

        // Get users and categories for filters
        $users = User::all();
        $categories = Category::all();

        return view('admin.direct-requests.index', compact('requests', 'stats', 'users', 'categories'));
    }

    /**
     * Show the form for creating a new direct request.
     * Requires 'direct_request.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('direct_request.create')) {
            abort(403, 'You do not have permission to create direct requests.');
        }

        $categories = Category::all();

        return view('admin.direct-requests.create', compact('categories'));
    }

    /**
     * Store a newly created direct request.
     * Requires 'direct_request.create' permission.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('direct_request.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create direct requests.'
                ], 403);
            }

            Log::info('Store request data:', $request->all());

            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'urgency' => 'required|in:low,medium,high,critical',
                'category_id' => 'nullable|exists:categories,id',
                'director_notes' => 'nullable|string',
            ]);

            // Create the request
            $directRequest = DirectRequest::create([
                'user_id' => auth()->id(),
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'urgency' => $validated['urgency'],
                'category_id' => $validated['category_id'] ?? null,
                'director_notes' => $validated['director_notes'] ?? null,
                'status' => 'pending_director',
            ]);

            Log::info('Direct request created:', ['id' => $directRequest->id]);

            // Create workflow entry
            try {
                RequestWorkflow::create([
                    'asset_request_id' => null,
                    'actor_id' => auth()->id(),
                    'from_department' => 'user',
                    'to_department' => 'approval',
                    'action' => 'submitted_direct',
                    'status_after' => 'pending_director',
                    'comments' => "Direct request submitted by " . auth()->user()->name,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create workflow: ' . $e->getMessage());
            }

            // Notify users with approval permission
            try {
                $approvers = User::permission('direct_request.approve')->get();
                foreach ($approvers as $approver) {
                    Notification::create([
                        'user_id' => $approver->id,
                        'type' => 'assignment',
                        'message' => "New direct request from {$directRequest->user->name}: {$directRequest->subject}",
                        'link' => route('admin.direct-requests.show', $directRequest->id),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to create notifications: ' . $e->getMessage());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Direct request created successfully!',
                    'request' => $directRequest
                ]);
            }

            return redirect()->route('admin.direct-requests.index')
                ->with('success', 'Direct request created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating direct request: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified direct request.
     * Requires 'direct_request.view' permission.
     */
    public function show($id)
    {
        try {
            if (!auth()->user()->can('direct_request.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view direct requests.'
                ], 403);
            }

            $directRequest = DirectRequest::with(['user', 'director', 'assetRequest', 'category'])->findOrFail($id);
            
            // Get workflows related to this direct request
            $workflows = RequestWorkflow::where('asset_request_id', $directRequest->asset_request_id)
                ->orWhere(function($query) use ($id) {
                    $query->whereNull('asset_request_id')
                          ->where('id', $id);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'request' => $directRequest,
                'workflows' => $workflows
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching direct request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load request details.'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified direct request.
     * Requires 'direct_request.edit' permission.
     */
    public function edit($id)
    {
        try {
            if (!auth()->user()->can('direct_request.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit direct requests.'
                ], 403);
            }

            $directRequest = DirectRequest::with('user')->findOrFail($id);
            
            // Only allow editing if status is pending_director or director_rejected
            if (!in_array($directRequest->status, ['pending_director', 'director_rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request cannot be edited.'
                ], 422);
            }

            $categories = Category::all();

            return response()->json([
                'success' => true,
                'directRequest' => $directRequest,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching direct request for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load request data.'
            ], 500);
        }
    }

    /**
     * Update the specified direct request.
     * Requires 'direct_request.edit' permission.
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('direct_request.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit direct requests.'
                ], 403);
            }

            $directRequest = DirectRequest::findOrFail($id);
            
            // Only allow editing if status is pending_director or director_rejected
            if (!in_array($directRequest->status, ['pending_director', 'director_rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request cannot be edited.'
                ], 422);
            }

            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'urgency' => 'required|in:low,medium,high,critical',
                'category_id' => 'nullable|exists:categories,id',
                'director_notes' => 'nullable|string',
            ]);

            $directRequest->update($validated);

            // Create workflow entry
            RequestWorkflow::create([
                'asset_request_id' => $directRequest->asset_request_id,
                'actor_id' => auth()->id(),
                'from_department' => 'user',
                'to_department' => 'approval',
                'action' => 'updated_direct',
                'status_before' => $directRequest->status,
                'status_after' => $directRequest->status,
                'comments' => "Direct request updated by " . auth()->user()->name,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Direct request updated successfully!',
                    'request' => $directRequest
                ]);
            }

            return redirect()->route('admin.direct-requests.index')
                ->with('success', 'Direct request updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating direct request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve the direct request.
     * Requires 'direct_request.approve' permission.
     */
    public function approve(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('direct_request.approve')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to approve direct requests.'
                ], 403);
            }

            $directRequest = DirectRequest::findOrFail($id);

            if ($directRequest->status !== 'pending_director') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request is not pending approval.'
                ], 422);
            }

            // Assign the approver as the current user
            $directRequest->update([
                'status' => 'director_approved',
                'director_id' => auth()->id(),
                'director_notes' => $request->notes ?? $directRequest->director_notes,
            ]);

            RequestWorkflow::create([
                'asset_request_id' => $directRequest->asset_request_id,
                'actor_id' => auth()->id(),
                'from_department' => 'approval',
                'to_department' => 'ict',
                'action' => 'approved_direct',
                'status_before' => 'pending_director',
                'status_after' => 'director_approved',
                'comments' => $request->notes ?? 'Direct request approved by ' . auth()->user()->name,
            ]);

            // Notify the user
            Notification::create([
                'user_id' => $directRequest->user_id,
                'type' => 'assignment',
                'message' => "Your direct request has been approved by " . auth()->user()->name,
                'link' => route('admin.direct-requests.show', $directRequest->id),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Direct request approved successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving direct request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request: ' . $e->getMessage()
            ], 500);
        }
    }

    
        public function reject(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('direct_request.reject')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to reject direct requests.'
                ], 403);
            }

            // No validation - just get the reason or use default
            $reason = $request->reason ?? 'Request rejected by ' . auth()->user()->name;

            $directRequest = DirectRequest::findOrFail($id);

            if ($directRequest->status !== 'pending_director') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request is not pending approval.'
                ], 422);
            }

            // Assign the rejector as the current user
            $directRequest->update([
                'status' => 'director_rejected',
                'director_id' => auth()->id(),
                'director_notes' => $reason,
            ]);

            // Create workflow entry
            RequestWorkflow::create([
                'asset_request_id' => null,
                'actor_id' => auth()->id(),
                'from_department' => 'approval',
                'to_department' => 'user',
                'action' => 'rejected_direct',
                'status_before' => 'pending_director',
                'status_after' => 'director_rejected',
                'comments' => $reason,
            ]);

            // Notify the user
            Notification::create([
                'user_id' => $directRequest->user_id,
                'type' => 'assignment',
                'message' => "Your direct request has been rejected by " . auth()->user()->name,
                'link' => route('admin.direct-requests.show', $directRequest->id),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Direct request rejected successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error rejecting direct request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forward the request to ICT.
     * Requires 'direct_request.forward_to_ict' permission.
     */
    public function forwardToICT(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('direct_request.forward_to_ict')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to forward requests to ICT.'
                ], 403);
            }

            $directRequest = DirectRequest::findOrFail($id);

            if ($directRequest->status !== 'director_approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request must be approved before forwarding to ICT.'
                ], 422);
            }

            $directRequest->update([
                'status' => 'sent_to_ict',
            ]);

            RequestWorkflow::create([
                'asset_request_id' => $directRequest->asset_request_id,
                'actor_id' => auth()->id(),
                'from_department' => 'approval',
                'to_department' => 'ict',
                'action' => 'forwarded_to_ict',
                'status_before' => 'director_approved',
                'status_after' => 'sent_to_ict',
                'comments' => $request->notes ?? 'Forwarded to ICT by ' . auth()->user()->name,
            ]);

            // Notify ICT users
            $ictUsers = User::permission('direct_request.specify')->get();
            foreach ($ictUsers as $ictUser) {
                Notification::create([
                    'user_id' => $ictUser->id,
                    'type' => 'assignment',
                    'message' => "Direct request forwarded to ICT: {$directRequest->subject}",
                    'link' => route('admin.direct-requests.show', $directRequest->id),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Request forwarded to ICT successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error forwarding to ICT: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to forward request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add specifications to the request.
     * Requires 'direct_request.specify' permission.
     */
    public function specify(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('direct_request.specify')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add specifications.'
                ], 403);
            }

            $request->validate([
                'specifications' => 'required|string',
            ]);

            $directRequest = DirectRequest::findOrFail($id);

            if ($directRequest->status !== 'sent_to_ict') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request must be with ICT before adding specifications.'
                ], 422);
            }

            $directRequest->update([
                'status' => 'ict_specified',
                'specifications_added' => $request->specifications,
                'ict_notes' => $request->notes ?? null,
            ]);

            RequestWorkflow::create([
                'asset_request_id' => $directRequest->asset_request_id,
                'actor_id' => auth()->id(),
                'from_department' => 'ict',
                'to_department' => 'approval',
                'action' => 'specified',
                'status_before' => 'sent_to_ict',
                'status_after' => 'ict_specified',
                'comments' => $request->notes ?? 'Specifications added by ' . auth()->user()->name,
            ]);

            // Notify the approver
            if ($directRequest->director_id) {
                Notification::create([
                    'user_id' => $directRequest->director_id,
                    'type' => 'assignment',
                    'message' => "Specifications added to direct request: {$directRequest->subject}",
                    'link' => route('admin.direct-requests.show', $directRequest->id),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Specifications added successfully!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding specifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add specifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forward the request to Procurement.
     * Requires 'direct_request.forward_to_procurement' permission.
     */
    public function forwardToProcurement(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('direct_request.forward_to_procurement')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to forward requests to Procurement.'
                ], 403);
            }

            $directRequest = DirectRequest::findOrFail($id);

            if ($directRequest->status !== 'ict_specified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request must have specifications before forwarding to Procurement.'
                ], 422);
            }

            $directRequest->update([
                'status' => 'director_sent_to_procurement',
            ]);

            RequestWorkflow::create([
                'asset_request_id' => $directRequest->asset_request_id,
                'actor_id' => auth()->id(),
                'from_department' => 'approval',
                'to_department' => 'procurement',
                'action' => 'forwarded_to_procurement',
                'status_before' => 'ict_specified',
                'status_after' => 'director_sent_to_procurement',
                'comments' => $request->notes ?? 'Forwarded to Procurement by ' . auth()->user()->name,
            ]);

            // Notify Procurement users
            $procurementUsers = User::permission('direct_request.procurement')->get();
            foreach ($procurementUsers as $procUser) {
                Notification::create([
                    'user_id' => $procUser->id,
                    'type' => 'assignment',
                    'message' => "Direct request forwarded to Procurement: {$directRequest->subject}",
                    'link' => route('admin.direct-requests.show', $directRequest->id),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Request forwarded to Procurement successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error forwarding to Procurement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to forward request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Merge the direct request to an asset request.
     * Requires 'direct_request.merge_to_request' permission.
     */
    public function merge(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('direct_request.merge_to_request')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to merge direct requests.'
                ], 403);
            }

            DB::beginTransaction();

            $directRequest = DirectRequest::findOrFail($id);

            if ($directRequest->status !== 'ict_specified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request must have specifications before merging.'
                ], 422);
            }

            // Create asset request from direct request
            $assetRequest = AssetRequest::create([
                'user_id' => $directRequest->user_id,
                'subject' => $directRequest->subject,
                'description' => $directRequest->description,
                'urgency' => $directRequest->urgency,
                'category_id' => $directRequest->category_id,
                'specifications' => $directRequest->specifications_added,
                'status' => 'draft',
                'direct_request_id' => $directRequest->id,
            ]);

            // Update direct request
            $directRequest->update([
                'status' => 'merged_to_request',
                'asset_request_id' => $assetRequest->id,
            ]);

            RequestWorkflow::create([
                'asset_request_id' => $assetRequest->id,
                'actor_id' => auth()->id(),
                'from_department' => 'approval',
                'to_department' => 'user',
                'action' => 'merged_direct',
                'status_before' => 'ict_specified',
                'status_after' => 'merged_to_request',
                'comments' => "Merged to asset request by " . auth()->user()->name,
            ]);

            // Notify the user
            Notification::create([
                'user_id' => $directRequest->user_id,
                'type' => 'assignment',
                'message' => "Your direct request has been merged to an asset request.",
                'link' => route('admin.asset-requests.show', $assetRequest->id),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Direct request merged to asset request successfully!',
                'asset_request_id' => $assetRequest->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error merging direct request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to merge request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified direct request.
     * Requires 'direct_request.delete' permission.
     */
    public function destroy($id)
    {
        try {
            if (!auth()->user()->can('direct_request.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete direct requests.'
                ], 403);
            }

            $directRequest = DirectRequest::findOrFail($id);

            // Only allow deletion if status is pending_director or director_rejected
            if (!in_array($directRequest->status, ['pending_director', 'director_rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request cannot be deleted.'
                ], 422);
            }

            $directRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Direct request deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting direct request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timeline for the specified direct request.
     * Requires 'direct_request.view' permission.
     */
    public function timeline($id)
    {
        try {
            if (!auth()->user()->can('direct_request.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view direct requests.'
                ], 403);
            }

            $directRequest = DirectRequest::with(['user', 'director'])->findOrFail($id);
            
            // Get workflows related to this direct request
            $workflows = RequestWorkflow::where('asset_request_id', $directRequest->asset_request_id)
                ->orWhere(function($query) use ($id) {
                    $query->whereNull('asset_request_id')
                          ->where('id', $id);
                })
                ->with('actor')
                ->orderBy('created_at', 'asc')
                ->get();

            $timeline = [];
            
            // Add creation event
            $timeline[] = [
                'event' => 'Request Created',
                'description' => "Direct request created by {$directRequest->user->name}",
                'date' => $directRequest->created_at->toISOString(),
                'actor' => $directRequest->user->name,
                'status' => $directRequest->status,
            ];

            // Add workflow events
            foreach ($workflows as $workflow) {
                $timeline[] = [
                    'event' => ucfirst(str_replace('_', ' ', $workflow->action)),
                    'description' => $workflow->comments ?? "Status changed to " . str_replace('_', ' ', $workflow->status_after),
                    'date' => $workflow->created_at->toISOString(),
                    'actor' => $workflow->actor->name ?? 'System',
                    'status' => $workflow->status_after,
                ];
            }

            // Sort by date
            usort($timeline, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            return response()->json([
                'success' => true,
                'timeline' => $timeline
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching timeline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load timeline.'
            ], 500);
        }
    }

    /**
     * Export direct requests to Excel.
     * Requires 'direct_request.view' permission.
     */
    public function export()
    {
        if (!auth()->user()->can('direct_request.view')) {
            abort(403, 'You do not have permission to export direct requests.');
        }

        // Export logic here
        // You can use Laravel Excel package
        return back()->with('info', 'Export functionality coming soon.');
    }
}
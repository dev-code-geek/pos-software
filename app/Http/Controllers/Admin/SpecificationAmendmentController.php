<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecificationAmendment;
use App\Models\AssetRequest;
use App\Models\User;
use App\Models\RequestWorkflow;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecificationAmendmentController extends Controller
{
    /**
     * Display a listing of specification amendments.
     * Requires 'asset_request.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('asset_request.view')) {
            abort(403, 'You do not have permission to view specification amendments.');
        }

        $query = SpecificationAmendment::with(['assetRequest', 'requestedBy', 'reviewedBy']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->has('requested_from') && $request->requested_from) {
            $query->where('requested_from', $request->requested_from);
        }

        // Filter by asset request
        if ($request->has('asset_request_id') && $request->asset_request_id) {
            $query->where('asset_request_id', $request->asset_request_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reason_for_revision', 'LIKE', "%{$search}%")
                  ->orWhere('new_specifications', 'LIKE', "%{$search}%")
                  ->orWhereHas('assetRequest', function($a) use ($search) {
                      $a->where('title', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('requestedBy', function($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $amendments = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total' => SpecificationAmendment::count(),
            'pending' => SpecificationAmendment::where('status', 'pending')->count(),
            'approved' => SpecificationAmendment::where('status', 'approved')->count(),
            'rejected' => SpecificationAmendment::where('status', 'rejected')->count(),
            'by_department' => SpecificationAmendment::select('requested_from', DB::raw('count(*) as total'))
                ->groupBy('requested_from')
                ->pluck('total', 'requested_from')
                ->toArray(),
            'today' => SpecificationAmendment::whereDate('created_at', today())->count(),
        ];

        $assetRequests = AssetRequest::all();
        $statuses = ['pending', 'approved', 'rejected'];
        $departments = ['ict', 'director', 'procurement'];

        return view('admin.specification-amendments.index', compact('amendments', 'stats', 'assetRequests', 'statuses', 'departments'));
    }

    /**
     * Show the form for creating a new specification amendment.
     * Requires 'asset_request.create' permission.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('asset_request.create')) {
            abort(403, 'You do not have permission to create specification amendments.');
        }

        $assetRequestId = $request->asset_request_id;
        $assetRequest = null;

        if ($assetRequestId) {
            $assetRequest = AssetRequest::with(['user', 'category'])->find($assetRequestId);
        }

        $assetRequests = AssetRequest::whereIn('status', [
            'procurement_reviewing', 'specs_revision_needed', 'sent_to_procurement'
        ])->get();

        $departments = ['ict', 'director', 'procurement'];

        return view('admin.specification-amendments.create', compact('assetRequest', 'assetRequests', 'departments'));
    }

    /**
     * Store a newly created specification amendment.
     * Requires 'asset_request.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('asset_request.create')) {
            return response()->json(['error' => 'You do not have permission to create specification amendments.'], 403);
        }

        $validated = $request->validate([
            'asset_request_id' => 'required|exists:asset_requests,id',
            'requested_from' => 'required|in:ict,director,procurement',
            'old_specifications' => 'nullable|string',
            'new_specifications' => 'required|string|min:10',
            'reason_for_revision' => 'required|string|min:10',
        ]);

        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'pending';

        $amendment = SpecificationAmendment::create($validated);

        // Update asset request status
        $assetRequest = AssetRequest::find($validated['asset_request_id']);
        $oldStatus = $assetRequest->status;
        $assetRequest->update(['status' => 'specs_revision_needed']);

        // Create workflow entry
        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => $validated['requested_from'],
            'to_department' => 'ict',
            'action' => 'specification_revision_requested',
            'status_before' => $oldStatus,
            'status_after' => 'specs_revision_needed',
            'comments' => "Specification revision requested. Reason: {$validated['reason_for_revision']}",
        ]);

        // Notify ICT staff
        $ictStaff = User::role('ict_officer')->get();
        foreach ($ictStaff as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'assignment',
                'message' => "Specification revision requested for request #{$assetRequest->id}: {$validated['reason_for_revision']}",
                'link' => route('admin.specification-amendments.show', $amendment->id),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Specification amendment created successfully!',
                'amendment' => $amendment
            ]);
        }

        return redirect()->route('admin.specification-amendments.index')
            ->with('success', 'Specification amendment created successfully!');
    }

    /**
     * Display the specified specification amendment.
     * Requires 'asset_request.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('asset_request.view')) {
            return response()->json(['error' => 'You do not have permission to view specification amendments.'], 403);
        }

        $amendment = SpecificationAmendment::with([
            'assetRequest' => function($q) {
                $q->with(['user', 'category']);
            },
            'requestedBy',
            'reviewedBy'
        ])->findOrFail($id);

        // Get workflow history
        $workflows = RequestWorkflow::where('asset_request_id', $amendment->asset_request_id)
            ->with('actor')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'amendment' => $amendment,
            'workflows' => $workflows,
        ]);
    }

    /**
     * Show the form for editing the specified specification amendment.
     * Requires 'asset_request.edit' permission.
     */
        public function edit($id)
        {
            if (!auth()->user()->can('asset_request.edit')) {
                abort(403, 'You do not have permission to edit specification amendments.');
            }

            try {
                $amendment = SpecificationAmendment::with(['assetRequest'])->findOrFail($id);
                
                // Only allow editing if pending
                if ($amendment->status !== 'pending') {
                    return response()->json([
                        'error' => 'Cannot edit a non-pending amendment.'
                    ], 422);
                }

                // Return the amendment data as JSON for the edit modal
                return response()->json([
                    'id' => $amendment->id,
                    'asset_request_id' => $amendment->asset_request_id,
                    'requested_from' => $amendment->requested_from,
                    'old_specifications' => $amendment->old_specifications,
                    'new_specifications' => $amendment->new_specifications,
                    'reason_for_revision' => $amendment->reason_for_revision,
                    'status' => $amendment->status,
                    'asset_request' => $amendment->assetRequest ? [
                        'id' => $amendment->assetRequest->id,
                        'title' => $amendment->assetRequest->title,
                    ] : null,
                ]);

            } catch (\Exception $e) {
                \Log::error('Error loading specification amendment for edit: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Failed to load amendment data: ' . $e->getMessage()
                ], 500);
            }
        }

    /**
     * Update the specified specification amendment.
     * Requires 'asset_request.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('asset_request.edit')) {
            return response()->json(['error' => 'You do not have permission to edit specification amendments.'], 403);
        }

        $amendment = SpecificationAmendment::findOrFail($id);

        if ($amendment->status !== 'pending') {
            return response()->json(['error' => 'Cannot edit a non-pending amendment.'], 422);
        }

        $validated = $request->validate([
            'asset_request_id' => 'required|exists:asset_requests,id',
            'requested_from' => 'required|in:ict,director,procurement',
            'old_specifications' => 'nullable|string',
            'new_specifications' => 'required|string|min:10',
            'reason_for_revision' => 'required|string|min:10',
        ]);

        $amendment->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Specification amendment updated successfully!',
                'amendment' => $amendment
            ]);
        }

        return redirect()->route('admin.specification-amendments.index')
            ->with('success', 'Specification amendment updated successfully!');
    }

    /**
     * Remove the specified specification amendment.
     * Requires 'asset_request.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('asset_request.delete')) {
            return response()->json(['error' => 'You do not have permission to delete specification amendments.'], 403);
        }

        $amendment = SpecificationAmendment::findOrFail($id);

        if ($amendment->status !== 'pending') {
            return response()->json(['error' => 'Cannot delete a non-pending amendment.'], 422);
        }

        $amendment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Specification amendment deleted successfully!'
        ]);
    }

    /**
     * Approve a specification amendment.
     * Requires 'asset_request.approve' permission.
     */
    public function approve(Request $request, $id)
    {
        if (!auth()->user()->can('asset_request.approve')) {
            return response()->json(['error' => 'You do not have permission to approve specification amendments.'], 403);
        }

        $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);

        $amendment = SpecificationAmendment::findOrFail($id);

        if ($amendment->status !== 'pending') {
            return response()->json(['error' => 'Only pending amendments can be approved.'], 422);
        }

        $oldStatus = $amendment->status;
        $amendment->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Update asset request with new specifications
        $assetRequest = AssetRequest::find($amendment->asset_request_id);
        $assetRequest->update([
            'specifications' => $amendment->new_specifications,
            'ict_notes' => ($assetRequest->ict_notes ? $assetRequest->ict_notes . "\n" : '') . 
                          "Specifications updated via amendment #{$amendment->id}: {$amendment->new_specifications}",
        ]);

        // Update asset request status
        $oldRequestStatus = $assetRequest->status;
        $assetRequest->update(['status' => 'sent_to_procurement']);

        // Create workflow entry
        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => 'procurement',
            'action' => 'specification_approved',
            'status_before' => $oldRequestStatus,
            'status_after' => 'sent_to_procurement',
            'comments' => $request->comments ?? "Specification amendment #{$amendment->id} approved",
        ]);

        // Notify procurement staff
        $procurementStaff = User::role('procurement_officer')->get();
        foreach ($procurementStaff as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'assignment',
                'message' => "Specifications updated for request #{$assetRequest->id}. Ready for procurement review.",
                'link' => route('admin.asset-requests.show', $assetRequest->id),
            ]);
        }

        // Notify the user
        Notification::create([
            'user_id' => $assetRequest->user_id,
            'type' => 'assignment',
            'message' => "Specifications for your request #{$assetRequest->id} have been approved and updated.",
            'link' => route('user.asset-requests.show', $assetRequest->id),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Specification amendment approved successfully!',
            'amendment' => $amendment
        ]);
    }

    /**
     * Reject a specification amendment.
     * Requires 'asset_request.approve' permission.
     */
    public function reject(Request $request, $id)
    {
        if (!auth()->user()->can('asset_request.approve')) {
            return response()->json(['error' => 'You do not have permission to reject specification amendments.'], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $amendment = SpecificationAmendment::findOrFail($id);

        if ($amendment->status !== 'pending') {
            return response()->json(['error' => 'Only pending amendments can be rejected.'], 422);
        }

        $oldStatus = $amendment->status;
        $amendment->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Create workflow entry
        $assetRequest = AssetRequest::find($amendment->asset_request_id);
        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'ict',
            'to_department' => $amendment->requested_from,
            'action' => 'specification_rejected',
            'status_before' => $oldStatus,
            'status_after' => 'rejected',
            'comments' => "Specification amendment rejected. Reason: {$request->reason}",
        ]);

        // Notify the requester
        Notification::create([
            'user_id' => $amendment->requested_by,
            'type' => 'assignment',
            'message' => "Your specification amendment request #{$amendment->id} has been rejected. Reason: {$request->reason}",
            'link' => route('admin.specification-amendments.show', $amendment->id),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Specification amendment rejected successfully!'
        ]);
    }

    /**
     * Bulk approve specification amendments.
     * Requires 'asset_request.approve' permission.
     */
    public function bulkApprove(Request $request)
    {
        if (!auth()->user()->can('asset_request.approve')) {
            return response()->json(['error' => 'You do not have permission to approve specification amendments.'], 403);
        }

        $request->validate([
            'amendment_ids' => 'required|array|min:1',
            'amendment_ids.*' => 'exists:specification_amendments,id',
            'comments' => 'nullable|string|max:500',
        ]);

        $amendments = SpecificationAmendment::whereIn('id', $request->amendment_ids)
            ->where('status', 'pending')
            ->get();

        if ($amendments->isEmpty()) {
            return response()->json(['error' => 'No pending amendments found to approve.'], 422);
        }

        $approved = 0;
        foreach ($amendments as $amendment) {
            $amendment->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Update asset request
            $assetRequest = AssetRequest::find($amendment->asset_request_id);
            if ($assetRequest) {
                $assetRequest->update([
                    'specifications' => $amendment->new_specifications,
                ]);
            }

            $approved++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$approved} specification amendment(s) approved successfully!"
        ]);
    }

    /**
     * Get specification amendments by asset request.
     * Requires 'asset_request.view' permission.
     */
    public function byAssetRequest($assetRequestId)
    {
        if (!auth()->user()->can('asset_request.view')) {
            return response()->json(['error' => 'You do not have permission to view specification amendments.'], 403);
        }

        $amendments = SpecificationAmendment::with(['requestedBy', 'reviewedBy'])
            ->where('asset_request_id', $assetRequestId)
            ->latest()
            ->get();

        $assetRequest = AssetRequest::find($assetRequestId);

        return response()->json([
            'asset_request' => $assetRequest->title ?? 'Unknown',
            'total' => $amendments->count(),
            'pending' => $amendments->where('status', 'pending')->count(),
            'approved' => $amendments->where('status', 'approved')->count(),
            'rejected' => $amendments->where('status', 'rejected')->count(),
            'amendments' => $amendments,
        ]);
    }

    /**
     * Get specification amendment statistics.
     * Requires 'asset_request.view' permission.
     */
    public function statistics()
    {
        if (!auth()->user()->can('asset_request.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => SpecificationAmendment::count(),
            'pending' => SpecificationAmendment::where('status', 'pending')->count(),
            'approved' => SpecificationAmendment::where('status', 'approved')->count(),
            'rejected' => SpecificationAmendment::where('status', 'rejected')->count(),
            'by_department' => SpecificationAmendment::select('requested_from', DB::raw('count(*) as total'))
                ->groupBy('requested_from')
                ->pluck('total', 'requested_from')
                ->toArray(),
            'today' => SpecificationAmendment::whereDate('created_at', today())->count(),
            'this_week' => SpecificationAmendment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => SpecificationAmendment::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'average_review_time' => SpecificationAmendment::whereNotNull('reviewed_at')
                ->get()
                ->avg(function($amendment) {
                    return $amendment->created_at->diffInHours($amendment->reviewed_at);
                }),
            'by_requester' => SpecificationAmendment::with('requestedBy')
                ->select('requested_by', DB::raw('count(*) as total'))
                ->groupBy('requested_by')
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
        $pendingAmendments = SpecificationAmendment::with(['assetRequest', 'requestedBy'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        $recentAmendments = SpecificationAmendment::with(['assetRequest', 'requestedBy'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'pending_amendments' => $pendingAmendments,
            'recent_amendments' => $recentAmendments,
            'total_pending' => SpecificationAmendment::where('status', 'pending')->count(),
            'total_approved' => SpecificationAmendment::where('status', 'approved')->count(),
            'total_rejected' => SpecificationAmendment::where('status', 'rejected')->count(),
            'total_amendments' => SpecificationAmendment::count(),
        ]);
    }

    /**
     * Export specification amendments to CSV/Excel.
     * Requires 'asset_request.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('asset_request.view')) {
            return response()->json(['error' => 'You do not have permission to export specification amendments.'], 403);
        }

        $amendments = SpecificationAmendment::with(['assetRequest', 'requestedBy', 'reviewedBy'])
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'Asset Request', 'Requested By', 'From Department',
            'Old Specifications', 'New Specifications', 'Reason',
            'Status', 'Reviewed By', 'Created At', 'Reviewed At'
        ];

        foreach ($amendments as $amendment) {
            $exportData[] = [
                $amendment->id,
                $amendment->assetRequest->title ?? 'N/A',
                $amendment->requestedBy->name ?? 'N/A',
                ucfirst(str_replace('_', ' ', $amendment->requested_from)),
                $amendment->old_specifications ?? 'None',
                $amendment->new_specifications,
                $amendment->reason_for_revision,
                ucfirst($amendment->status),
                $amendment->reviewedBy->name ?? 'N/A',
                $amendment->created_at->format('Y-m-d H:i:s'),
                $amendment->reviewed_at ? $amendment->reviewed_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $amendments->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get pending specification amendments.
     * Requires 'asset_request.view' permission.
     */
    public function pending()
    {
        if (!auth()->user()->can('asset_request.view')) {
            return response()->json(['error' => 'You do not have permission to view specification amendments.'], 403);
        }

        $amendments = SpecificationAmendment::with(['assetRequest', 'requestedBy'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json([
            'total' => $amendments->count(),
            'amendments' => $amendments,
        ]);
    }

    /**
     * Get approved specification amendments.
     * Requires 'asset_request.view' permission.
     */
    public function approved()
    {
        if (!auth()->user()->can('asset_request.view')) {
            return response()->json(['error' => 'You do not have permission to view specification amendments.'], 403);
        }

        $amendments = SpecificationAmendment::with(['assetRequest', 'requestedBy', 'reviewedBy'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json([
            'total' => $amendments->count(),
            'amendments' => $amendments,
        ]);
    }

    /**
     * Get rejected specification amendments.
     * Requires 'asset_request.view' permission.
     */
    public function rejected()
    {
        if (!auth()->user()->can('asset_request.view')) {
            return response()->json(['error' => 'You do not have permission to view specification amendments.'], 403);
        }

        $amendments = SpecificationAmendment::with(['assetRequest', 'requestedBy', 'reviewedBy'])
            ->where('status', 'rejected')
            ->latest()
            ->get();

        return response()->json([
            'total' => $amendments->count(),
            'amendments' => $amendments,
        ]);
    }
}
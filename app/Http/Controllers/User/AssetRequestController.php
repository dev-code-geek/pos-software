<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AssetRequest;
use App\Models\Category;
use App\Models\RequestWorkflow;
use Illuminate\Http\Request;

class AssetRequestController extends Controller
{
    public function index()
    {
        $requests = AssetRequest::where('user_id', auth()->id())
            ->with('category')
            ->latest()
            ->paginate(10);

        $stats = [
            'pending' => AssetRequest::where('user_id', auth()->id())
                ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
                ->count(),
            'approved' => AssetRequest::where('user_id', auth()->id())
                ->where('status', 'approved_by_ict')
                ->count(),
        ];

        $categories = Category::all();

        return view('user.asset-requests.index', compact('requests', 'stats', 'categories'));
    }

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
            'from_department' => 'user',
            'to_department' => 'ict',
            'action' => 'submitted',
            'status_after' => 'pending_ict_review',
            'comments' => 'Request submitted by user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request submitted successfully!'
        ]);
    }

    public function show($id)
    {
        $request = AssetRequest::where('user_id', auth()->id())
            ->with(['category', 'assignedProduct'])
            ->findOrFail($id);

        return response()->json($request);
    }

    public function edit($id)
    {
        $request = AssetRequest::where('user_id', auth()->id())
            ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
            ->findOrFail($id);

        return response()->json($request);
    }

    public function update(Request $request, $id)
    {
        $assetRequest = AssetRequest::where('user_id', auth()->id())
            ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
            ->findOrFail($id);

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

        $assetRequest->update($validated);

        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'user',
            'to_department' => 'ict',
            'action' => 'updated',
            'status_before' => $assetRequest->status,
            'status_after' => $assetRequest->status,
            'comments' => 'Request updated by user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request updated successfully!'
        ]);
    }

    public function cancel($id)
    {
        $assetRequest = AssetRequest::where('user_id', auth()->id())
            ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
            ->findOrFail($id);

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

        return response()->json([
            'success' => true,
            'message' => 'Request cancelled successfully!'
        ]);
    }
}
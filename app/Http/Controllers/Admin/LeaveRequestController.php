<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('leave_request.view')) {
            abort(403, 'You do not have permission to view leave requests.');
        }

        $query = LeaveRequest::with(['employee', 'leaveType', 'approver', 'rejecter', 'requester']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('start_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('end_date', '<=', $request->to_date);
        }

        // appends() keeps the active filters attached to pagination links
        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        $employees  = Employee::where('is_active', 1)->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('is_active', 1)->orderBy('leave_name')->get();
        $statuses   = ['Pending', 'Approved', 'Rejected', 'Cancelled'];

        $stats = [
            'pending'   => LeaveRequest::where('approval_status', 'Pending')->count(),
            'approved'  => LeaveRequest::where('approval_status', 'Approved')->count(),
            'rejected'  => LeaveRequest::where('approval_status', 'Rejected')->count(),
            'cancelled' => LeaveRequest::where('approval_status', 'Cancelled')->count(),
            'total'     => LeaveRequest::count(),
        ];

        return view('admin.leave-requests.index', compact('leaveRequests', 'stats', 'employees', 'leaveTypes', 'statuses'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('leave_request.create')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $validated = $request->validate([
            'employee_id'           => 'required|exists:employees,id',
            'leave_type_id'         => 'required|exists:leave_types,id',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'reason'                => 'required|string|max:1000',
            'contact_during_leave'  => 'nullable|string|max:50',
            'medical_certificate'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $start = Carbon::parse($validated['start_date']);
            $end   = Carbon::parse($validated['end_date']);
            $totalDays = $start->diffInDays($end) + 1;

            $balance = LeaveBalance::where('employee_id', $validated['employee_id'])
                ->where('leave_type_id', $validated['leave_type_id'])
                ->where('year', $start->year)
                ->first();

            if (!$balance) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No leave balance found for this employee and leave type.'], 422);
            }

            if ($balance->remaining_days < $totalDays) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient leave balance. Available: {$balance->remaining_days} days, Requested: {$totalDays} days.",
                ], 422);
            }

            if ($this->hasOverlap($validated['employee_id'], $validated['start_date'], $validated['end_date'])) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Employee already has a pending or approved leave request for this period.'], 422);
            }

            $medicalPath = null;
            if ($request->hasFile('medical_certificate')) {
                $file = $request->file('medical_certificate');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $medicalPath = $file->storeAs('medical-certificates/' . date('Y/m'), $fileName, 'public');
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id'              => $validated['employee_id'],
                'leave_type_id'            => $validated['leave_type_id'],
                'start_date'               => $validated['start_date'],
                'end_date'                 => $validated['end_date'],
                'total_days'               => $totalDays,
                'reason'                   => $validated['reason'],
                'contact_during_leave'     => $validated['contact_during_leave'] ?? null,
                'medical_certificate_path' => $medicalPath,
                'approval_status'          => 'Pending',
                'requested_by'             => auth()->id(),
                'requested_at'             => Carbon::now(),
                'created_by'               => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'       => true,
                    'message'       => 'Leave request submitted successfully.',
                    'leave_request' => $leaveRequest,
                ]);
            }

            return redirect()->route('admin.leave.requests.index')->with('success', 'Leave request submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave request creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to submit leave request. Please try again.'], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('leave_request.view')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $leaveRequest = LeaveRequest::with(['employee', 'leaveType', 'approver', 'rejecter', 'requester'])->find($id);

        if (!$leaveRequest) {
            return response()->json(['success' => false, 'message' => 'Leave request not found.'], 404);
        }

        return response()->json($leaveRequest);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('leave_request.edit')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json(['success' => false, 'message' => 'Leave request not found.'], 404);
        }

        if ($leaveRequest->approval_status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be edited.'], 422);
        }

        return response()->json([
            'leave_request' => $leaveRequest,
            'employees'     => Employee::where('is_active', 1)->orderBy('first_name')->get(),
            'leave_types'   => LeaveType::where('is_active', 1)->orderBy('leave_name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('leave_request.edit')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json(['success' => false, 'message' => 'Leave request not found.'], 404);
        }

        if ($leaveRequest->approval_status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be edited.'], 422);
        }

        $validated = $request->validate([
            'employee_id'          => 'required|exists:employees,id',
            'leave_type_id'        => 'required|exists:leave_types,id',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
            'reason'               => 'required|string|max:1000',
            'contact_during_leave' => 'nullable|string|max:50',
            'medical_certificate'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $start = Carbon::parse($validated['start_date']);
            $end   = Carbon::parse($validated['end_date']);
            $totalDays = $start->diffInDays($end) + 1;

            if ($this->hasOverlap($validated['employee_id'], $validated['start_date'], $validated['end_date'], $id)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Employee already has a pending or approved leave request for this period.'], 422);
            }

            $medicalPath = $leaveRequest->medical_certificate_path;
            if ($request->hasFile('medical_certificate')) {
                if ($medicalPath && Storage::disk('public')->exists($medicalPath)) {
                    Storage::disk('public')->delete($medicalPath);
                }
                $file = $request->file('medical_certificate');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $medicalPath = $file->storeAs('medical-certificates/' . date('Y/m'), $fileName, 'public');
            }

            $leaveRequest->update([
                'employee_id'              => $validated['employee_id'],
                'leave_type_id'            => $validated['leave_type_id'],
                'start_date'               => $validated['start_date'],
                'end_date'                 => $validated['end_date'],
                'total_days'               => $totalDays,
                'reason'                   => $validated['reason'],
                'contact_during_leave'     => $validated['contact_during_leave'] ?? null,
                'medical_certificate_path' => $medicalPath,
                'updated_by'               => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'       => true,
                    'message'       => 'Leave request updated successfully.',
                    'leave_request' => $leaveRequest,
                ]);
            }

            return redirect()->route('admin.leave.requests.index')->with('success', 'Leave request updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave request update failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update leave request. Please try again.'], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('leave_request.delete')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $result = $this->performDelete($id);
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 422));
    }

    public function approve($id)
    {
        if (!auth()->user()->can('leave_request.approve')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $result = $this->performApprove($id);
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 422));
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->can('leave_request.reject')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $result = $this->performReject($id, $request->rejection_reason);
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 422));
    }

    public function cancel($id)
    {
        if (!auth()->user()->can('leave_request.cancel')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json(['success' => false, 'message' => 'Leave request not found.'], 404);
        }

        if ($leaveRequest->approval_status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be cancelled.'], 422);
        }

        $leaveRequest->update([
            'approval_status' => 'Cancelled',
            'updated_by'      => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request cancelled successfully.',
            'status'  => 'Cancelled',
        ]);
    }

    public function bulkApprove(Request $request)
    {
        if (!auth()->user()->can('leave_request.bulk_approve')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:leave_requests,id',
        ]);

        [$succeeded, $failed] = $this->runBulk($request->ids, fn ($id) => $this->performApprove($id));

        return response()->json([
            'success' => true,
            'message' => $this->bulkMessage($succeeded, $failed, 'approved'),
        ]);
    }

    public function bulkReject(Request $request)
    {
        if (!auth()->user()->can('leave_request.bulk_reject')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $request->validate([
            'ids'              => 'required|array|min:1',
            'ids.*'            => 'exists:leave_requests,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        [$succeeded, $failed] = $this->runBulk($request->ids, fn ($id) => $this->performReject($id, $request->rejection_reason));

        return response()->json([
            'success' => true,
            'message' => $this->bulkMessage($succeeded, $failed, 'rejected'),
        ]);
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('leave_request.bulk_delete')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:leave_requests,id',
        ]);

        [$succeeded, $failed] = $this->runBulk($request->ids, fn ($id) => $this->performDelete($id));

        return response()->json([
            'success' => true,
            'message' => $this->bulkMessage($succeeded, $failed, 'deleted'),
        ]);
    }

    public function downloadCertificate($id)
    {
        if (!auth()->user()->can('leave_request.view')) {
            abort(403);
        }

        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest || !$leaveRequest->medical_certificate_path) {
            abort(404, 'No medical certificate found.');
        }

        $path = storage_path('app/public/' . $leaveRequest->medical_certificate_path);

        if (!file_exists($path)) {
            abort(404, 'Medical certificate file not found.');
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';

        return response()->download($path, 'medical_certificate_' . $id . '.' . $extension);
    }

    public function getEmployeeBalance($employeeId, Request $request)
    {
        if (!auth()->user()->can('leave_request.view')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $leaveTypeId = $request->get('leave_type_id');
        $year = $request->get('year', date('Y'));

        if (!$leaveTypeId) {
            return response()->json(['success' => false, 'message' => 'Leave type ID required.'], 422);
        }

        $balance = LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        return response()->json([
            'success' => true,
            'balance' => $balance ? $balance->remaining_days : 0,
        ]);
    }

    /**
     * Shared overlap check, optionally excluding a request id (used by update()).
     */
    private function hasOverlap($employeeId, $startDate, $endDate, $excludeId = null): bool
    {
        return LeaveRequest::where('employee_id', $employeeId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereIn('approval_status', ['Pending', 'Approved'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            })->exists();
    }

    /**
     * Core approve logic. No permission check here — callers (approve() and
     * bulkApprove()) each check the permission that's appropriate for them,
     * so bulk-approving no longer silently requires the single-approve
     * permission too.
     */
    private function performApprove($id): array
    {
        DB::beginTransaction();

        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                DB::rollBack();
                return ['success' => false, 'message' => "Request #{$id}: not found.", 'code' => 404];
            }

            if ($leaveRequest->approval_status !== 'Pending') {
                DB::rollBack();
                return ['success' => false, 'message' => "Request #{$id}: only pending requests can be approved.", 'code' => 422];
            }

            $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', Carbon::parse($leaveRequest->start_date)->year)
                ->first();

            if (!$balance) {
                DB::rollBack();
                return ['success' => false, 'message' => "Request #{$id}: leave balance not found.", 'code' => 422];
            }

            if ($balance->remaining_days < $leaveRequest->total_days) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Request #{$id}: insufficient leave balance (available {$balance->remaining_days}, requested {$leaveRequest->total_days}).",
                    'code'    => 422,
                ];
            }

            $leaveRequest->update([
                'approval_status' => 'Approved',
                'approved_by'     => auth()->id(),
                'approved_at'     => Carbon::now(),
                'updated_by'      => auth()->id(),
            ]);

            $balance->used_days += $leaveRequest->total_days;
            $balance->remaining_days = $balance->entitled_days - $balance->used_days + $balance->carried_over_days;
            $balance->last_updated = Carbon::now();
            $balance->updated_by = auth()->id();
            $balance->save();

            DB::commit();

            return ['success' => true, 'message' => 'Leave request approved successfully.', 'status' => 'Approved'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Leave request #{$id} approval failed: " . $e->getMessage());
            return ['success' => false, 'message' => "Request #{$id}: failed to approve.", 'code' => 500];
        }
    }

    private function performReject($id, string $reason): array
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return ['success' => false, 'message' => "Request #{$id}: not found.", 'code' => 404];
        }

        if ($leaveRequest->approval_status !== 'Pending') {
            return ['success' => false, 'message' => "Request #{$id}: only pending requests can be rejected.", 'code' => 422];
        }

        $leaveRequest->update([
            'approval_status'  => 'Rejected',
            'rejected_by'      => auth()->id(),
            'rejected_at'      => Carbon::now(),
            'rejection_reason' => $reason,
            'updated_by'       => auth()->id(),
        ]);

        return ['success' => true, 'message' => 'Leave request rejected successfully.', 'status' => 'Rejected'];
    }

    private function performDelete($id): array
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return ['success' => false, 'message' => "Request #{$id}: not found.", 'code' => 404];
        }

        if (!in_array($leaveRequest->approval_status, ['Pending', 'Cancelled'])) {
            return ['success' => false, 'message' => "Request #{$id}: only pending or cancelled requests can be deleted.", 'code' => 422];
        }

        if ($leaveRequest->medical_certificate_path) {
            Storage::disk('public')->delete($leaveRequest->medical_certificate_path);
        }

        $leaveRequest->delete();

        return ['success' => true, 'message' => 'Leave request deleted successfully.'];
    }

    /**
     * Runs a callback over a list of ids, splitting successes from failures.
     */
    private function runBulk(array $ids, callable $callback): array
    {
        $succeeded = 0;
        $failed = [];

        foreach ($ids as $id) {
            $result = $callback($id);
            if (!empty($result['success'])) {
                $succeeded++;
            } else {
                $failed[] = $result['message'] ?? "Request #{$id}: failed.";
            }
        }

        return [$succeeded, $failed];
    }

    private function bulkMessage(int $succeeded, array $failed, string $verb): string
    {
        $message = "{$succeeded} request(s) {$verb} successfully.";
        if (!empty($failed)) {
            $message .= ' ' . count($failed) . ' failed: ' . implode(' | ', $failed);
        }
        return $message;
    }
}
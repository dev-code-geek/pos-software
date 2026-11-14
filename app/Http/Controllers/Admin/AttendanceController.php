<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('attendance.view')) {
            abort(403, 'You do not have permission to view attendance.');
        }

        $query = Attendance::with(['employee', 'approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('attendance_status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }

        if ($request->has('archived') && $request->archived == 1) {
            $query->onlyTrashed();
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(15);

        $stats = [
            'present' => Attendance::whereDate('attendance_date', today())->where('attendance_status', 'Present')->count(),
            'absent' => Attendance::whereDate('attendance_date', today())->where('attendance_status', 'Absent')->count(),
            'late' => Attendance::whereDate('attendance_date', today())->where('attendance_status', 'Late')->count(),
            'on_leave' => Attendance::whereDate('attendance_date', today())->where('attendance_status', 'On Leave')->count(),
            'on_travel' => Attendance::whereDate('attendance_date', today())->where('attendance_status', 'On Travel')->count(),
            'total' => Attendance::whereDate('attendance_date', today())->count(),
        ];

        $employees = Employee::where('is_active', 1)->get();
        $statuses = ['Present', 'Absent', 'Late', 'Half Day', 'On Leave', 'On Travel'];

        return view('admin.attendance.index', compact('attendances', 'stats', 'employees', 'statuses'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('attendance.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date|unique:attendance,attendance_date,NULL,id,employee_id,' . $request->employee_id,
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'attendance_status' => 'required|in:Present,Absent,Late,Half Day,On Leave,On Travel',
            'remarks' => 'nullable|string|max:500',
        ]);

        $totalWorkedHours = null;
        $overtimeHours = 0;

        if ($request->filled('check_in_time') && $request->filled('check_out_time')) {
            $checkIn = Carbon::parse($request->check_in_time);
            $checkOut = Carbon::parse($request->check_out_time);
            $minutes = $checkOut->diffInMinutes($checkIn);
            $totalWorkedHours = round($minutes / 60, 2);
            if ($minutes > 480) {
                $overtimeHours = round(($minutes - 480) / 60, 2);
            }
        }

        $validated['total_worked_hours'] = $totalWorkedHours;
        $validated['overtime_hours'] = $overtimeHours;
        $validated['created_by'] = auth()->id();

        $attendance = Attendance::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'attendance' => $attendance
            ]);
        }

        return redirect()->route('admin.attendance.index')->with('success', 'Attendance recorded successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->can('attendance.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $attendance = Attendance::with(['employee', 'approver'])->findOrFail($id);
        return response()->json($attendance);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('attendance.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $attendance = Attendance::with('employee')->findOrFail($id);
        $employees = Employee::where('is_active', 1)->get();

        return response()->json([
            'attendance' => $attendance,
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('attendance.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date|unique:attendance,attendance_date,' . $id . ',id,employee_id,' . $request->employee_id,
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'attendance_status' => 'required|in:Present,Absent,Late,Half Day,On Leave,On Travel',
            'remarks' => 'nullable|string|max:500',
        ]);

        $totalWorkedHours = null;
        $overtimeHours = 0;

        if ($request->filled('check_in_time') && $request->filled('check_out_time')) {
            $checkIn = Carbon::parse($request->check_in_time);
            $checkOut = Carbon::parse($request->check_out_time);
            $minutes = $checkOut->diffInMinutes($checkIn);
            $totalWorkedHours = round($minutes / 60, 2);
            if ($minutes > 480) {
                $overtimeHours = round(($minutes - 480) / 60, 2);
            }
        }

        $validated['total_worked_hours'] = $totalWorkedHours;
        $validated['overtime_hours'] = $overtimeHours;
        $validated['updated_by'] = auth()->id();

        $attendance->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully.',
                'attendance' => $attendance
            ]);
        }

        return redirect()->route('admin.attendance.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('attendance.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Attendance deleted successfully.']);
    }

    public function approve($id)
    {
        if (!auth()->user()->can('attendance.approve')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $attendance = Attendance::findOrFail($id);

        if ($attendance->is_approved) {
            return response()->json(['error' => 'Attendance is already approved.'], 422);
        }

        $attendance->update([
            'is_approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance approved successfully.'
        ]);
    }

    public function bulkStore(Request $request)
    {
        if (!auth()->user()->can('attendance.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $request->validate([
            'attendances' => 'required|array|min:1',
            'attendances.*.employee_id' => 'required|exists:employees,id',
            'attendances.*.attendance_date' => 'required|date',
            'attendances.*.check_in_time' => 'nullable|date_format:H:i',
            'attendances.*.check_out_time' => 'nullable|date_format:H:i',
            'attendances.*.attendance_status' => 'required|in:Present,Absent,Late,Half Day,On Leave,On Travel',
        ]);

        DB::beginTransaction();

        try {
            $created = 0;
            $duplicates = [];
            $errors = [];

            foreach ($request->attendances as $index => $data) {
                try {
                    $exists = Attendance::where('employee_id', $data['employee_id'])
                        ->where('attendance_date', $data['attendance_date'])
                        ->exists();

                    if ($exists) {
                        $employee = Employee::find($data['employee_id']);
                        $duplicates[] = "Row " . ($index + 1) . ": " . ($employee->first_name ?? 'Unknown') . " already has attendance for this date";
                        continue;
                    }

                    $totalWorkedHours = null;
                    $overtimeHours = 0;

                    if (!empty($data['check_in_time']) && !empty($data['check_out_time'])) {
                        $checkIn = Carbon::parse($data['check_in_time']);
                        $checkOut = Carbon::parse($data['check_out_time']);
                        if ($checkOut <= $checkIn) {
                            $errors[] = "Row " . ($index + 1) . ": Check out time must be after check in time";
                            continue;
                        }
                        $minutes = $checkOut->diffInMinutes($checkIn);
                        $totalWorkedHours = round($minutes / 60, 2);
                        if ($minutes > 480) {
                            $overtimeHours = round(($minutes - 480) / 60, 2);
                        }
                    }

                    Attendance::create([
                        'employee_id' => $data['employee_id'],
                        'attendance_date' => $data['attendance_date'],
                        'check_in_time' => $data['check_in_time'] ?? null,
                        'check_out_time' => $data['check_out_time'] ?? null,
                        'total_worked_hours' => $totalWorkedHours,
                        'overtime_hours' => $overtimeHours,
                        'attendance_status' => $data['attendance_status'],
                        'remarks' => $data['remarks'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "{$created} attendance records created successfully.";
            if (!empty($duplicates)) {
                $message .= " Duplicates: " . implode(', ', $duplicates);
            }
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk attendance store failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save attendance records: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        if (!auth()->user()->can('attendance.view')) {
            abort(403);
        }

        $query = Attendance::with('employee');

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('attendance_status', $request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $filename = 'attendance_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Date', 'Check In', 'Check Out', 'Hours', 'Overtime', 'Status', 'Remarks', 'Approved']);
            foreach ($attendances as $att) {
                fputcsv($handle, [
                    $att->employee->full_name ?? 'N/A',
                    $att->attendance_date->format('Y-m-d'),
                    $att->check_in_time ?? '-',
                    $att->check_out_time ?? '-',
                    $att->total_worked_hours ? $att->total_worked_hours . ' hrs' : '-',
                    $att->overtime_hours ? $att->overtime_hours . ' hrs' : '-',
                    $att->attendance_status,
                    $att->remarks ?? '-',
                    $att->is_approved ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
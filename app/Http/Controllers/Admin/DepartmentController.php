<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\AssetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     * Requires 'department.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('department.view')) {
            abort(403, 'You do not have permission to view departments.');
        }

        $query = Department::withCount(['users']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('head_of_department', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        if ($request->has('sort')) {
            $sortField = $request->sort;
            $sortDirection = $request->direction ?? 'asc';
            
            if (in_array($sortField, ['name', 'users_count', 'created_at'])) {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $departments = $query->paginate(15);

        // Stats
        $stats = [
            'total' => Department::count(),
            'with_users' => Department::has('users')->count(),
            'without_users' => Department::doesntHave('users')->count(),
        ];

        // Get department head names for display
        $departmentHeads = User::whereIn('id', $departments->pluck('head_of_department'))
            ->pluck('name', 'id')
            ->toArray();

        return view('admin.departments.index', compact('departments', 'stats', 'departmentHeads'));
    }

    /**
     * Show the form for creating a new department.
     * Requires 'department.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('department.create')) {
            abort(403, 'You do not have permission to create departments.');
        }

        // Get users who can be department heads
        $potentialHeads = User::role(['admin', 'manager', 'director'])->get();

        return view('admin.departments.create', compact('potentialHeads'));
    }

    /**
     * Store a newly created department.
     * Requires 'department.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('department.create')) {
            return response()->json(['error' => 'You do not have permission to create departments.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments',
            'description' => 'nullable|string|max:500',
            'head_of_department' => 'nullable|exists:users,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $department = Department::create($validated);

        // If a head was assigned, update the user's department
        if ($request->head_of_department) {
            User::where('id', $request->head_of_department)->update([
                'department_id' => $department->id
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Department created successfully!',
                'department' => $department
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully!');
    }

    /**
     * Display the specified department.
     * Requires 'department.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('department.view')) {
            return response()->json(['error' => 'You do not have permission to view departments.'], 403);
        }

        $department = Department::with(['users' => function($query) {
                $query->select('id', 'name', 'email', 'employee_id', 'created_at');
            }])
            ->withCount(['users'])
            ->findOrFail($id);

        // Get department head name
        $headName = $department->head_of_department 
            ? User::where('id', $department->head_of_department)->value('name') 
            : null;

        // Get request statistics for this department
        $requestStats = [
            'total' => AssetRequest::whereHas('user', function($q) use ($id) {
                $q->where('department_id', $id);
            })->count(),
            'pending' => AssetRequest::whereHas('user', function($q) use ($id) {
                $q->where('department_id', $id);
            })->whereIn('status', ['pending_ict_review', 'ict_reviewing'])->count(),
            'approved' => AssetRequest::whereHas('user', function($q) use ($id) {
                $q->where('department_id', $id);
            })->where('status', 'approved_by_ict')->count(),
        ];

        // Get recent users from this department
        $recentUsers = User::where('department_id', $id)
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        return response()->json([
            'department' => $department,
            'head_name' => $headName,
            'request_stats' => $requestStats,
            'recent_users' => $recentUsers,
            'users_count' => $department->users_count,
        ]);
    }

    /**
     * Show the form for editing the specified department.
     * Requires 'department.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('department.edit')) {
            abort(403, 'You do not have permission to edit departments.');
        }

        $department = Department::findOrFail($id);
        
        // Get users who can be department heads
        $potentialHeads = User::role(['admin', 'manager', 'director'])->get();
        
        // Get current head name
        $currentHeadName = $department->head_of_department 
            ? User::where('id', $department->head_of_department)->value('name') 
            : null;

        return view('admin.departments.edit', compact('department', 'potentialHeads', 'currentHeadName'));
    }

    /**
     * Update the specified department.
     * Requires 'department.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('department.edit')) {
            return response()->json(['error' => 'You do not have permission to edit departments.'], 403);
        }

        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $id,
            'description' => 'nullable|string|max:500',
            'head_of_department' => 'nullable|exists:users,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $oldHead = $department->head_of_department;
        $department->update($validated);

        // Update head of department
        if ($oldHead != $request->head_of_department) {
            // Remove old head's department
            if ($oldHead) {
                User::where('id', $oldHead)->update(['department_id' => null]);
            }
            
            // Assign new head
            if ($request->head_of_department) {
                User::where('id', $request->head_of_department)->update([
                    'department_id' => $department->id
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully!',
                'department' => $department
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department.
     * Requires 'department.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('department.delete')) {
            return response()->json(['error' => 'You do not have permission to delete departments.'], 403);
        }

        $department = Department::findOrFail($id);

        // Check if department has users
        $userCount = User::where('department_id', $id)->count();
        if ($userCount > 0) {
            return response()->json([
                'error' => "Cannot delete department with {$userCount} associated user(s). Reassign users first."
            ], 422);
        }

        // Check if department has asset requests
        $requestCount = AssetRequest::whereHas('user', function($q) use ($id) {
            $q->where('department_id', $id);
        })->count();

        if ($requestCount > 0) {
            return response()->json([
                'error' => "Cannot delete department with {$requestCount} associated asset request(s)."
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully!'
        ]);
    }

    /**
     * Bulk delete departments.
     * Requires 'department.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('department.delete')) {
            return response()->json(['error' => 'You do not have permission to delete departments.'], 403);
        }

        $request->validate([
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'exists:departments,id',
        ]);

        $departments = Department::whereIn('id', $request->department_ids)
            ->withCount(['users'])
            ->get();

        // Check if any department has users
        $departmentsWithUsers = $departments->filter(function($dept) {
            return $dept->users_count > 0;
        });

        if ($departmentsWithUsers->isNotEmpty()) {
            $names = $departmentsWithUsers->pluck('name')->implode(', ');
            return response()->json([
                'error' => "Cannot delete departments with associated users: {$names}"
            ], 422);
        }

        $deletedCount = Department::whereIn('id', $request->department_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} department(s) deleted successfully!"
        ]);
    }

    /**
     * Get departments dropdown list for select inputs.
     */
    public function dropdown(Request $request)
    {
        $departments = Department::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    /**
     * Get department statistics.
     * Requires 'department.view' permission.
     */
    public function statistics()
    {
        if (!auth()->user()->can('department.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => Department::count(),
            'with_users' => Department::has('users')->count(),
            'without_users' => Department::doesntHave('users')->count(),
            'top_departments' => Department::withCount('users')
                ->orderBy('users_count', 'desc')
                ->limit(5)
                ->get(),
            'user_distribution' => DB::table('users')
                ->join('departments', 'users.department_id', '=', 'departments.id')
                ->select('departments.name', DB::raw('count(users.id) as total'))
                ->groupBy('departments.name')
                ->pluck('total', 'departments.name')
                ->toArray(),
            'request_distribution' => DB::table('asset_requests')
                ->join('users', 'asset_requests.user_id', '=', 'users.id')
                ->join('departments', 'users.department_id', '=', 'departments.id')
                ->select('departments.name', DB::raw('count(asset_requests.id) as total'))
                ->groupBy('departments.name')
                ->pluck('total', 'departments.name')
                ->toArray(),
            'department_heads' => Department::whereNotNull('head_of_department')->count(),
            'departments_without_heads' => Department::whereNull('head_of_department')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export departments to CSV/Excel.
     * Requires 'department.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('department.view')) {
            return response()->json(['error' => 'You do not have permission to export departments.'], 403);
        }

        $departments = Department::withCount('users')->get();
        
        // Prepare export data
        $exportData = [];
        $exportData[] = ['ID', 'Name', 'Description', 'Head of Department', 'Email', 'Phone', 'User Count', 'Created At'];

        foreach ($departments as $dept) {
            $headName = $dept->head_of_department 
                ? User::where('id', $dept->head_of_department)->value('name') 
                : 'N/A';

            $exportData[] = [
                $dept->id,
                $dept->name,
                $dept->description ?? '',
                $headName,
                $dept->email ?? '',
                $dept->phone ?? '',
                $dept->users_count,
                $dept->created_at->format('Y-m-d H:i:s'),
            ];
        }

        // For now, return JSON. You can implement CSV/Excel export later.
        return response()->json([
            'data' => $exportData,
            'count' => count($departments),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Import departments from CSV/Excel.
     * Requires 'department.create' permission.
     */
    public function import(Request $request)
    {
        if (!auth()->user()->can('department.create')) {
            return response()->json(['error' => 'You do not have permission to import departments.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        // Store the file temporarily
        $path = $request->file('file')->store('temp', 'public');
        
        return response()->json([
            'success' => true,
            'message' => 'Import feature coming soon!',
            'file' => $path,
        ]);
    }

    /**
     * Check if a department name exists.
     */
    public function checkName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'except_id' => 'nullable|exists:departments,id',
        ]);

        $exists = Department::where('name', $request->name)
            ->when($request->except_id, function($query) use ($request) {
                return $query->where('id', '!=', $request->except_id);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Department name already exists' : 'Department name is available',
        ]);
    }

    /**
     * Get departments for the current user.
     */
    public function myDepartment()
    {
        $user = auth()->user();
        
        if (!$user->department_id) {
            return response()->json(['message' => 'You are not assigned to any department.'], 404);
        }

        $department = Department::with(['users' => function($query) {
                $query->select('id', 'name', 'email', 'employee_id');
            }])
            ->withCount(['users'])
            ->findOrFail($user->department_id);

        return response()->json($department);
    }

    /**
     * Get users by department.
     * Requires 'department.view' permission.
     */
    public function users($id)
    {
        if (!auth()->user()->can('department.view')) {
            return response()->json(['error' => 'You do not have permission to view department users.'], 403);
        }

        $department = Department::findOrFail($id);
        
        $users = User::where('department_id', $id)
            ->with('roles')
            ->get(['id', 'name', 'email', 'employee_id', 'created_at']);

        return response()->json([
            'department' => $department->name,
            'total_users' => $users->count(),
            'users' => $users,
        ]);
    }

    /**
     * Assign a user to a department.
     * Requires 'department.edit' permission.
     */
    public function assignUser(Request $request, $id)
    {
        if (!auth()->user()->can('department.edit')) {
            return response()->json(['error' => 'You do not have permission to assign users.'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $department = Department::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        // Remove user from previous department if any
        if ($user->department_id) {
            $oldDepartment = Department::find($user->department_id);
            // If the user was the head, remove head status
            if ($oldDepartment && $oldDepartment->head_of_department == $user->id) {
                $oldDepartment->update(['head_of_department' => null]);
            }
        }

        $user->update(['department_id' => $department->id]);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} assigned to {$department->name} successfully!",
        ]);
    }

    /**
     * Remove a user from a department.
     * Requires 'department.edit' permission.
     */
    public function removeUser(Request $request, $id)
    {
        if (!auth()->user()->can('department.edit')) {
            return response()->json(['error' => 'You do not have permission to remove users.'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $department = Department::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        if ($user->department_id != $department->id) {
            return response()->json(['error' => 'User is not in this department.'], 422);
        }

        // If user is the head, remove head status
        if ($department->head_of_department == $user->id) {
            $department->update(['head_of_department' => null]);
        }

        $user->update(['department_id' => null]);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} removed from {$department->name} successfully!",
        ]);
    }

    /**
     * Get department activity summary.
     * Requires 'department.view' permission.
     */
    public function activity($id)
    {
        if (!auth()->user()->can('department.view')) {
            return response()->json(['error' => 'You do not have permission to view department activity.'], 403);
        }

        $department = Department::findOrFail($id);

        // Get user IDs in this department
        $userIds = User::where('department_id', $id)->pluck('id');

        $activity = [
            'department' => $department->name,
            'total_users' => $userIds->count(),
            'asset_requests' => [
                'total' => AssetRequest::whereIn('user_id', $userIds)->count(),
                'pending' => AssetRequest::whereIn('user_id', $userIds)
                    ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
                    ->count(),
                'approved' => AssetRequest::whereIn('user_id', $userIds)
                    ->where('status', 'approved_by_ict')
                    ->count(),
                'rejected' => AssetRequest::whereIn('user_id', $userIds)
                    ->where('status', 'rejected_by_ict')
                    ->count(),
                'assigned' => AssetRequest::whereIn('user_id', $userIds)
                    ->where('status', 'assigned_to_user')
                    ->count(),
            ],
            'assignments' => [
                'active' => Assignment::whereIn('user_id', $userIds)
                    ->whereNull('actual_return_date')
                    ->count(),
                'returned' => Assignment::whereIn('user_id', $userIds)
                    ->whereNotNull('actual_return_date')
                    ->count(),
                'overdue' => Assignment::whereIn('user_id', $userIds)
                    ->whereNull('actual_return_date')
                    ->where('expected_return_date', '<', now())
                    ->count(),
            ],
        ];

        return response()->json($activity);
    }

    /**
     * Get widget data for dashboard.
     */
    public function widget()
    {
        $topDepartments = Department::withCount('users')
            ->orderBy('users_count', 'desc')
            ->limit(5)
            ->get();

        $recentDepartments = Department::latest()
            ->limit(5)
            ->get();

        return response()->json([
            'top_departments' => $topDepartments,
            'recent_departments' => $recentDepartments,
            'total_departments' => Department::count(),
        ]);
    }

    /**
     * Reorder departments.
     * Requires 'department.edit' permission.
     */
    public function reorder(Request $request)
    {
        if (!auth()->user()->can('department.edit')) {
            return response()->json(['error' => 'You do not have permission to reorder departments.'], 403);
        }

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:departments,id',
        ]);

        foreach ($request->order as $index => $id) {
            Department::where('id', $id)->update(['order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Departments reordered successfully!'
        ]);
    }
}
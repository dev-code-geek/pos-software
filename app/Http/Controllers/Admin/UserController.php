<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserController extends Controller
{
    /**
     * FIX: centralized exception handler.
     *
     * Every write action (store/update/destroy/role sync) is wrapped in
     * try/catch. On failure we ALWAYS log the full exception to
     * storage/logs/laravel.log, and we return the real exception message
     * (+ file/line/class + short trace) in the JSON response, but ONLY
     * when APP_DEBUG=true in your .env. In production (APP_DEBUG=false)
     * the response stays generic so you don't leak internals to end
     * users — but you'll still get the full detail in the log either way.
     */
    private function errorResponse(Throwable $e, string $context): \Illuminate\Http\JsonResponse
    {
        Log::error("[$context] " . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $payload = [
            'success' => false,
            'message' => "Failed: $context. Please try again.",
        ];

        if (config('app.debug')) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => collect(explode("\n", $e->getTraceAsString()))->take(8)->all(),
            ];
        }

        return response()->json($payload, 500);
    }

    /**
     * Display a listing of users.
     * Requires 'user.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('user.view')) {
            abort(403, 'You do not have permission to view users.');
        }

        $query = User::with(['department', 'roles']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('employee_id', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->role($request->role);
        }

        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by verification status
        if ($request->has('verified')) {
            if ($request->verified === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->verified === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        if ($request->has('sort')) {
            $sortField = $request->sort;
            $sortDirection = $request->direction ?? 'asc';
            
            if (in_array($sortField, ['name', 'email', 'created_at', 'employee_id'])) {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->latest();
        }

        $users = $query->paginate(15);

        $departments = Department::all();
        $roles = Role::all();

        // Stats
        $stats = [
            'total' => User::count(),
            'admins' => User::role(['admin', 'super_admin'])->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'new_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'users_with_roles' => User::has('roles')->count(),
            'users_without_roles' => User::doesntHave('roles')->count(),
            'by_department' => Department::withCount('users')
                ->orderBy('users_count', 'desc')
                ->limit(5)
                ->get(),
            'active_today' => User::whereDate('updated_at', today())->count(),
        ];

        return view('admin.users.index', compact('users', 'departments', 'roles', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     * Requires 'user.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('user.create')) {
            abort(403, 'You do not have permission to create users.');
        }

        $departments = Department::all();
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('group_name');

        return view('admin.users.create', compact('departments', 'roles', 'permissions'));
    }

    /**
     * Store a newly created user.
     * Requires 'user.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('user.create')) {
            return response()->json(['error' => 'You do not have permission to create users.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|string|max:50|unique:users',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $roleIds = $validated['roles'] ?? [];
            $permissionIds = $validated['permissions'] ?? [];
            unset($validated['roles'], $validated['permissions']);

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            // Assign roles
            if (!empty($roleIds)) {
                $roles = Role::whereIn('id', $roleIds)->get();
                $user->syncRoles($roles);
            }

            // Assign direct permissions
            if (!empty($permissionIds)) {
                $permissions = Permission::whereIn('id', $permissionIds)->get();
                $user->syncPermissions($permissions);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully!',
                    'user' => $user
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully!');
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'creating user');
        }
    }

    /**
     * Display the specified user.
     * Requires 'user.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('user.view')) {
            return response()->json(['error' => 'You do not have permission to view users.'], 403);
        }

        try {
            $user = User::with(['department', 'roles', 'permissions'])
                ->findOrFail($id);

            // Get user activity stats
            $stats = [
                'assignments' => $user->assignments()->count(),
                'active_assignments' => $user->assignments()->whereNull('actual_return_date')->count(),
                'asset_requests' => $user->assetRequests()->count(),
                'pending_requests' => $user->assetRequests()
                    ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
                    ->count(),
                'maintenance_logs' => $user->maintenanceLogs()->count(),
                'direct_requests' => $user->directRequests()->count(),
            ];

            return response()->json([
                'user' => $user,
                'stats' => $stats,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'loading user');
        }
    }

     /**
     * Show the form for editing the specified user.
     * Requires 'user.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('user.edit')) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit users.'
                ], 403);
            }
            abort(403, 'You do not have permission to edit users.');
        }

        try {
            $user = User::with(['roles', 'permissions'])->findOrFail($id);
            
            // Log the data for debugging
            \Log::info('User edit data:', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'department_id' => $user->department_id,
                'roles' => $user->roles->pluck('name'),
            ]);

            // For AJAX requests, return JSON
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($user);
            }

            // For normal view requests
            $departments = Department::all();
            $roles = Role::all();
            $permissions = Permission::all()->groupBy('group_name');

            return view('admin.users.edit', compact('user', 'departments', 'roles', 'permissions'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('User not found for edit:', ['id' => $id]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found.'
                ], 404);
            }
            abort(404, 'User not found.');
            
        } catch (Throwable $e) {
            \Log::error('Edit user error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load user data: ' . $e->getMessage()
                ], 500);
            }
            abort(500, 'Failed to load user data.');
        }
    }

    /**
     * Update the specified user.
     * Requires 'user.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('user.edit')) {
            return response()->json(['error' => 'You do not have permission to edit users.'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $roleIds = $validated['roles'] ?? [];
            $permissionIds = $validated['permissions'] ?? [];
            unset($validated['roles'], $validated['permissions']);

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // Sync roles
            $roles = Role::whereIn('id', $roleIds)->get();
            $user->syncRoles($roles);

            // Sync direct permissions
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            $user->syncPermissions($permissions);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully!',
                    'user' => $user
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully!');
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'updating user');
        }
    }

    /**
     * Remove the specified user.
     * Requires 'user.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('user.delete')) {
            return response()->json(['error' => 'You do not have permission to delete users.'], 403);
        }

        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                return response()->json(['error' => 'Cannot delete your own account.'], 422);
            }

            // Check if user has active assignments
            $activeAssignments = $user->assignments()->whereNull('actual_return_date')->count();
            if ($activeAssignments > 0) {
                return response()->json([
                    'error' => "Cannot delete user with {$activeAssignments} active assignment(s). Reassign or return assets first."
                ], 422);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'deleting user');
        }
    }

    /**
     * Bulk delete users.
     * Requires 'user.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('user.delete')) {
            return response()->json(['error' => 'You do not have permission to delete users.'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Check if any users have active assignments
        $usersWithAssignments = User::whereIn('id', $request->user_ids)
            ->whereHas('assignments', function($q) {
                $q->whereNull('actual_return_date');
            })
            ->pluck('name')
            ->toArray();

        if (!empty($usersWithAssignments)) {
            return response()->json([
                'error' => "Cannot delete users with active assignments: " . implode(', ', $usersWithAssignments)
            ], 422);
        }

        // Prevent deleting current user
        if (in_array(auth()->id(), $request->user_ids)) {
            return response()->json([
                'error' => 'Cannot delete your own account.'
            ], 422);
        }

        $deletedCount = User::whereIn('id', $request->user_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} user(s) deleted successfully!"
        ]);
    }

    /**
     * Assign role to a user.
     * Requires 'user.assign_role' permission.
     */
    public function assignRole(Request $request, $id)
    {
        if (!auth()->user()->can('user.assign_role')) {
            return response()->json(['error' => 'You do not have permission to assign roles.'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        try {
            $user = User::findOrFail($id);
            
            if ($user->hasRole($validated['role'])) {
                return response()->json(['error' => 'User already has this role.'], 422);
            }

            $user->assignRole($validated['role']);

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully!'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'assigning role');
        }
    }

    /**
     * Remove role from a user.
     * Requires 'user.remove_role' permission.
     */
    public function removeRole(Request $request, $id)
    {
        if (!auth()->user()->can('user.remove_role')) {
            return response()->json(['error' => 'You do not have permission to remove roles.'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        try {
            $user = User::findOrFail($id);

            if (!$user->hasRole($validated['role'])) {
                return response()->json(['error' => 'User does not have this role.'], 422);
            }

            // Prevent removing super_admin role from self
            if ($validated['role'] === 'super_admin' && $user->id === auth()->id()) {
                return response()->json(['error' => 'Cannot remove super_admin role from yourself.'], 422);
            }

            $user->removeRole($validated['role']);

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully!'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'removing role');
        }
    }

    /**
     * Manage user permissions.
     * Requires 'user.manage_permissions' permission.
     */
    public function managePermissions($id)
    {
        if (!auth()->user()->can('user.manage_permissions')) {
            abort(403, 'You do not have permission to manage user permissions.');
        }

        $user = User::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy('group_name');

        return view('admin.users.permissions', compact('user', 'permissions'));
    }

    /**
     * Sync permissions for a user.
     * Requires 'user.manage_permissions' permission.
     */
    public function syncPermissions(Request $request, $id)
    {
        if (!auth()->user()->can('user.manage_permissions')) {
            return response()->json(['error' => 'You do not have permission to manage permissions.'], 403);
        }

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $user = User::findOrFail($id);
            $permissions = Permission::whereIn('id', $validated['permissions'] ?? [])->get();
            $user->syncPermissions($permissions);

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully!'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'syncing permissions');
        }
    }

    /**
     * Get user statistics.
     * Requires 'user.view' permission.
     */
    public function statistics()
    {
        if (!auth()->user()->can('user.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => User::count(),
            'by_role' => DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('count(*) as total'))
                ->groupBy('roles.name')
                ->pluck('total', 'roles.name')
                ->toArray(),
            'by_department' => Department::withCount('users')
                ->orderBy('users_count', 'desc')
                ->get(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'users_with_roles' => User::has('roles')->count(),
            'users_without_roles' => User::doesntHave('roles')->count(),
            'users_with_permissions' => User::has('permissions')->count(),
            'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_this_month' => User::where('created_at', '>=', now()->subMonth())->count(),
            'active_today' => User::whereDate('updated_at', today())->count(),
            'active_this_week' => User::where('updated_at', '>=', now()->subWeek())->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export users to CSV/Excel.
     * Requires 'user.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('user.view')) {
            return response()->json(['error' => 'You do not have permission to export users.'], 403);
        }

        $users = User::with(['department', 'roles'])
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'Name', 'Email', 'Employee ID', 'Department', 'Roles',
            'Status', 'Created At', 'Last Updated'
        ];

        foreach ($users as $user) {
            $exportData[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->employee_id ?? 'N/A',
                $user->department->name ?? 'N/A',
                $user->roles->pluck('name')->implode(', ') ?: 'No Roles',
                $user->email_verified_at ? 'Verified' : 'Unverified',
                $user->created_at->format('Y-m-d H:i:s'),
                $user->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $users->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Import users from CSV/Excel.
     * Requires 'user.create' permission.
     */
    public function import(Request $request)
    {
        if (!auth()->user()->can('user.create')) {
            return response()->json(['error' => 'You do not have permission to import users.'], 403);
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
     * Verify a user's email.
     * Requires 'user.edit' permission.
     */
    public function verifyEmail($id)
    {
        if (!auth()->user()->can('user.edit')) {
            return response()->json(['error' => 'You do not have permission to verify users.'], 403);
        }

        try {
            $user = User::findOrFail($id);
            
            if ($user->email_verified_at) {
                return response()->json(['error' => 'User is already verified.'], 422);
            }

            $user->update(['email_verified_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'User verified successfully!'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'verifying user');
        }
    }

    /**
     * Unverify a user's email.
     * Requires 'user.edit' permission.
     */
    public function unverifyEmail($id)
    {
        if (!auth()->user()->can('user.edit')) {
            return response()->json(['error' => 'You do not have permission to unverify users.'], 403);
        }

        try {
            $user = User::findOrFail($id);

            if (!$user->email_verified_at) {
                return response()->json(['error' => 'User is already unverified.'], 422);
            }

            $user->update(['email_verified_at' => null]);

            return response()->json([
                'success' => true,
                'message' => 'User unverified successfully!'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'unverifying user');
        }
    }

    /**
     * Get users dropdown for select inputs.
     * Requires 'user.view' permission.
     */
    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('user.view')) {
            return response()->json(['error' => 'You do not have permission to view users.'], 403);
        }

        $query = User::select('id', 'name', 'email');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('role') && $request->role) {
            $query->role($request->role);
        }

        $users = $query->orderBy('name')->limit(50)->get();

        return response()->json($users);
    }

    /**
     * Get user activity log.
     * Requires 'user.view' permission.
     */
    public function activity($id)
    {
        if (!auth()->user()->can('user.view')) {
            return response()->json(['error' => 'You do not have permission to view user activity.'], 403);
        }

        $user = User::findOrFail($id);

        $activities = [
            'assignments' => $user->assignments()->with(['product', 'assignedBy'])
                ->latest()
                ->limit(10)
                ->get(),
            'asset_requests' => $user->assetRequests()->with(['category'])
                ->latest()
                ->limit(10)
                ->get(),
            'maintenance_logs' => $user->maintenanceLogs()->with(['product'])
                ->latest()
                ->limit(10)
                ->get(),
            'workflows' => $user->workflowActions()->with(['assetRequest'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return response()->json($activities);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentUsers = User::with(['department', 'roles'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total' => User::count(),
            'new_today' => User::whereDate('created_at', today())->count(),
            'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
        ];

        return response()->json([
            'recent_users' => $recentUsers,
            'stats' => $stats,
        ]);
    }

    /**
     * Get user profile for the current user.
     */
    public function profile()
    {
        $user = auth()->user()->load(['department', 'roles', 'permissions']);

        return response()->json($user);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            if (isset($validated['new_password'])) {
                $validated['password'] = Hash::make($validated['new_password']);
                unset($validated['new_password']);
            }
            unset($validated['current_password']);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $user
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'updating profile');
        }
    }
}
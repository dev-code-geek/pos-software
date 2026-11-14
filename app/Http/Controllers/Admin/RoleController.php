<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     * Requires 'role.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('role.view')) {
            abort(403, 'You do not have permission to view roles.');
        }

        $query = Role::with('permissions')->withCount('users');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by guard
        if ($request->has('guard') && $request->guard) {
            $query->where('guard_name', $request->guard);
        }

        $roles = $query->latest()->paginate(15);

        $permissions = Permission::all()->groupBy('group_name');

        $stats = [
            'total' => Role::count(),
            'total_users_with_roles' => Role::withCount('users')->get()->sum('users_count'),
            'custom_roles' => Role::where('name', '!=', 'super_admin')->count(),
            'total_permissions' => Permission::count(),
            'unassigned_permissions' => Permission::doesntHave('roles')->count(),
        ];

        // Get all users for role assignment modal
        $users = User::all();

        return view('admin.roles.index', compact('roles', 'permissions', 'stats', 'users'));
    }

    /**
     * Show the form for creating a new role.
     * Requires 'role.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('role.create')) {
            abort(403, 'You do not have permission to create roles.');
        }

        $permissions = Permission::all()->groupBy('group_name');

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     * Requires 'role.create' permission.
     *
     * NOTE: guard_name is no longer accepted from the client — this app
     * only uses the 'web' guard, so it's hardcoded here rather than
     * requiring a field the create form never actually collected.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('role.create')) {
            return response()->json(['error' => 'You do not have permission to create roles.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $permissions = Permission::whereIn('id', $validated['permissions'] ?? [])->get();
        $role->syncPermissions($permissions);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully!',
                'role' => $role
            ]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully!');
    }

    /**
     * Display the specified role.
     * Requires 'role.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view roles.'], 403);
        }

        $role = Role::with('permissions')->withCount('users')->findOrFail($id);

        // Get users with this role
        $users = User::role($role->name)->get(['id', 'name', 'email']);

        return response()->json([
            'role' => $role,
            'users' => $users,
            'permissions' => $role->permissions,
            'users_count' => $role->users_count,
        ]);
    }

    /**
     * Show the form for editing the specified role.
     * Requires 'role.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('role.edit')) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit roles.'
                ], 403);
            }
            abort(403, 'You do not have permission to edit roles.');
        }

        try {
            $role = Role::with('permissions')->findOrFail($id);

            // Get all permissions grouped for the view
            $permissions = Permission::all()->groupBy('group_name');

            // For AJAX requests, return JSON with proper structure
            if (request()->ajax() || request()->wantsJson()) {
                $response = [
                    'success' => true,
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('id')->toArray(),
                    'all_permissions' => $permissions,
                ];

                return response()->json($response);
            }

            // For normal view requests
            return view('admin.roles.edit', compact('role', 'permissions'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Role not found for edit:', ['id' => $id]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Role not found.'
                ], 404);
            }
            abort(404, 'Role not found.');

        } catch (\Exception $e) {
            \Log::error('Edit role error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load role data: ' . $e->getMessage()
                ], 500);
            }
            abort(500, 'Failed to load role data.');
        }
    }

    /**
     * Update the specified role.
     * Requires 'role.edit' permission.
     *
     * NOTE: guard_name is intentionally left untouched here — it's never
     * submitted by the edit form, and we don't want an absent field to
     * silently overwrite whatever guard the role already has.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('role.edit')) {
            return response()->json(['error' => 'You do not have permission to edit roles.'], 403);
        }

        $role = Role::findOrFail($id);

        if ($role->name === 'super_admin' && $request->name !== 'super_admin') {
            return response()->json(['error' => 'Cannot rename super_admin role.'], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $permissions = Permission::whereIn('id', $validated['permissions'] ?? [])->get();
        $role->syncPermissions($permissions);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully!',
                'role' => $role
            ]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role.
     * Requires 'role.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('role.delete')) {
            return response()->json(['error' => 'You do not have permission to delete roles.'], 403);
        }

        $role = Role::findOrFail($id);

        if ($role->name === 'super_admin') {
            return response()->json(['error' => 'Cannot delete super_admin role.'], 422);
        }

        if ($role->users()->count() > 0) {
            return response()->json([
                'error' => "Cannot delete role with {$role->users()->count()} assigned user(s)."
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully!'
        ]);
    }

    /**
     * Bulk delete roles.
     * Requires 'role.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('role.delete')) {
            return response()->json(['error' => 'You do not have permission to delete roles.'], 403);
        }

        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        // Check if any are system roles or have users
        $systemRoles = Role::whereIn('id', $request->role_ids)
            ->where('name', 'super_admin')
            ->pluck('name')
            ->toArray();

        if (!empty($systemRoles)) {
            return response()->json([
                'error' => "Cannot delete system roles: " . implode(', ', $systemRoles)
            ], 422);
        }

        // Check if any role has users
        $rolesWithUsers = Role::whereIn('id', $request->role_ids)
            ->whereHas('users')
            ->pluck('name')
            ->toArray();

        if (!empty($rolesWithUsers)) {
            return response()->json([
                'error' => "Cannot delete roles with assigned users: " . implode(', ', $rolesWithUsers)
            ], 422);
        }

        $deletedCount = Role::whereIn('id', $request->role_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} role(s) deleted successfully!"
        ]);
    }

    /**
     * Assign role to a user.
     * Requires 'user.assign_role' permission.
     */
    public function assignToUser(Request $request)
    {
        if (!auth()->user()->can('user.assign_role')) {
            return response()->json(['error' => 'You do not have permission to assign roles to users.'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        if ($user->hasRole($role->name)) {
            return response()->json(['error' => 'User already has this role.'], 422);
        }

        $user->assignRole($role->name);

        return response()->json([
            'success' => true,
            'message' => "Role {$role->name} assigned to {$user->name} successfully!"
        ]);
    }

    /**
     * Remove role from a user.
     * Requires 'user.remove_role' permission.
     */
    public function removeFromUser(Request $request)
    {
        if (!auth()->user()->can('user.remove_role')) {
            return response()->json(['error' => 'You do not have permission to remove roles from users.'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        if (!$user->hasRole($role->name)) {
            return response()->json(['error' => 'User does not have this role.'], 422);
        }

        if ($role->name === 'super_admin' && $user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot remove super_admin role from yourself.'], 422);
        }

        $user->removeRole($role->name);

        return response()->json([
            'success' => true,
            'message' => "Role {$role->name} removed from {$user->name} successfully!"
        ]);
    }

    /**
     * Get users for a specific role.
     * Requires 'role.view' permission.
     */
    public function users($id)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view roles.'], 403);
        }

        $role = Role::findOrFail($id);
        $users = User::role($role->name)->get(['id', 'name', 'email']);

        return response()->json([
            'role' => $role->name,
            'users_count' => $users->count(),
            'users' => $users,
        ]);
    }

    /**
     * Get role statistics.
     * Requires 'role.view' permission.
     */
    public function statistics()
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => Role::count(),
            'by_guard' => Role::select('guard_name', DB::raw('count(*) as total'))
                ->groupBy('guard_name')
                ->pluck('total', 'guard_name')
                ->toArray(),
            'users_with_roles' => User::whereHas('roles')->count(),
            'users_without_roles' => User::doesntHave('roles')->count(),
            'permissions_assigned' => DB::table('role_has_permissions')->count(),
            'roles_without_permissions' => Role::doesntHave('permissions')->count(),
            'roles_without_users' => Role::doesntHave('users')->count(),
            'most_used_role' => Role::withCount('users')
                ->orderBy('users_count', 'desc')
                ->first(),
            'permission_usage' => Permission::withCount('roles')
                ->orderBy('roles_count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentRoles = Role::withCount('users')
            ->latest()
            ->limit(5)
            ->get();

        $totalRoles = Role::count();
        $totalUsers = User::count();
        $usersWithRoles = User::whereHas('roles')->count();

        return response()->json([
            'recent_roles' => $recentRoles,
            'total_roles' => $totalRoles,
            'total_users' => $totalUsers,
            'users_with_roles' => $usersWithRoles,
            'users_without_roles' => $totalUsers - $usersWithRoles,
            'roles_with_users' => Role::has('users')->count(),
            'roles_without_users' => Role::doesntHave('users')->count(),
        ]);
    }

    /**
     * Clone a role (create a copy with same permissions).
     * Requires 'role.create' permission.
     */
    public function clone(Request $request, $id)
    {
        if (!auth()->user()->can('role.create')) {
            return response()->json(['error' => 'You do not have permission to clone roles.'], 403);
        }

        $role = Role::with('permissions')->findOrFail($id);

        $request->validate([
            'new_name' => 'required|string|max:255|unique:roles,name',
        ]);

        $newRole = Role::create([
            'name' => $request->new_name,
            'guard_name' => $role->guard_name,
        ]);

        $newRole->syncPermissions($role->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Role cloned successfully!',
            'role' => $newRole,
        ]);
    }

    /**
     * Export roles to CSV/Excel.
     * Requires 'role.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to export roles.'], 403);
        }

        $roles = Role::withCount('users')->get();

        $exportData = [];
        $exportData[] = ['ID', 'Name', 'Guard', 'Permissions Count', 'Users Count', 'Created At'];

        foreach ($roles as $role) {
            $exportData[] = [
                $role->id,
                $role->name,
                $role->guard_name ?? 'web',
                $role->permissions->count(),
                $role->users_count,
                $role->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $roles->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get permissions for a role (API).
     * Requires 'role.view' permission.
     */
    public function permissions($id)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view roles.'], 403);
        }

        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'role' => $role->name,
            'permissions' => $role->permissions,
            'permission_count' => $role->permissions->count(),
        ]);
    }

    /**
     * Sync permissions for a role (API).
     * Requires 'role.edit' permission.
     */
    public function syncPermissions(Request $request, $id)
    {
        if (!auth()->user()->can('role.edit')) {
            return response()->json(['error' => 'You do not have permission to update role permissions.'], 403);
        }

        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = Permission::whereIn('id', $request->permissions ?? [])->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions synced successfully!',
            'role' => $role->name,
            'assigned_permissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Get roles dropdown for select inputs.
     * Requires 'role.view' permission.
     */
    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view roles.'], 403);
        }

        $query = Role::select('id', 'name');

        if ($request->has('guard') && $request->guard) {
            $query->where('guard_name', $request->guard);
        }

        if ($request->has('search') && $request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        $roles = $query->orderBy('name')->get();

        return response()->json($roles);
    }

    /**
     * Check if role name exists.
     */
    public function checkName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'except_id' => 'nullable|exists:roles,id',
        ]);

        $exists = Role::where('name', $request->name)
            ->when($request->except_id, function($query) use ($request) {
                return $query->where('id', '!=', $request->except_id);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Role name already exists' : 'Role name is available',
        ]);
    }
}
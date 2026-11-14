<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     * Requires 'role.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('role.view')) {
            abort(403, 'You do not have permission to view permissions.');
        }

        $query = Permission::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('group_name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by group
        if ($request->has('group') && $request->group) {
            $query->where('group_name', $request->group);
        }

        // Get permissions with pagination
        $permissions = $query->orderBy('group_name')->orderBy('name')->paginate(15);
        
        // Group permissions for display
        $groupedPermissions = $permissions->groupBy('group_name');

        // Get all unique groups for filter dropdown
        $groups = Permission::select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name')
            ->toArray();

        // Get permission counts
        $stats = [
            'total' => Permission::count(),
            'by_group' => Permission::select('group_name', DB::raw('count(*) as total'))
                ->groupBy('group_name')
                ->pluck('total', 'group_name')
                ->toArray(),
        ];

        // Get all permissions as flat collection for the view
        $allPermissions = Permission::orderBy('group_name')->orderBy('name')->get();

        return view('admin.permissions.index', compact('permissions', 'groupedPermissions', 'groups', 'stats', 'allPermissions'));
    }

    /**
     * Show the form for creating a new permission.
     * Requires 'role.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('role.create')) {
            abort(403, 'You do not have permission to create permissions.');
        }

        // Get existing groups for dropdown
        $groups = Permission::select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name')
            ->toArray();

        return view('admin.permissions.create', compact('groups'));
    }

    /**
     * Store a newly created permission.
     * Requires 'role.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('role.create')) {
            return response()->json(['error' => 'You do not have permission to create permissions.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'group_name' => 'required|string|max:100',
            'guard_name' => 'nullable|string|in:web,api',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'group_name' => $validated['group_name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully!',
                'permission' => $permission
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully!');
    }

    /**
     * Display the specified permission.
     * Requires 'role.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view permissions.'], 403);
        }

        $permission = Permission::findOrFail($id);
        
        // Get roles that have this permission
        $roles = Role::whereHas('permissions', function($q) use ($id) {
            $q->where('permission_id', $id);
        })->get();

        return response()->json([
            'permission' => $permission,
            'assigned_to_roles' => $roles,
        ]);
    }

    /**
     * Show the form for editing the specified permission.
     * Requires 'role.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('role.edit')) {
            abort(403, 'You do not have permission to edit permissions.');
        }

        $permission = Permission::findOrFail($id);
        
        // Get existing groups for dropdown
        $groups = Permission::select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name')
            ->toArray();

        return view('admin.permissions.edit', compact('permission', 'groups'));
    }

    /**
     * Update the specified permission.
     * Requires 'role.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('role.edit')) {
            return response()->json(['error' => 'You do not have permission to edit permissions.'], 403);
        }

        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'group_name' => 'required|string|max:100',
            'guard_name' => 'nullable|string|in:web,api',
        ]);

        $permission->update([
            'name' => $validated['name'],
            'group_name' => $validated['group_name'],
            'guard_name' => $validated['guard_name'] ?? $permission->guard_name,
        ]);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully!',
                'permission' => $permission
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully!');
    }

    /**
     * Remove the specified permission.
     * Requires 'role.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('role.delete')) {
            return response()->json(['error' => 'You do not have permission to delete permissions.'], 403);
        }

        $permission = Permission::findOrFail($id);
        
        // Check if permission is assigned to any role
        $roleCount = Role::whereHas('permissions', function($q) use ($id) {
            $q->where('permission_id', $id);
        })->count();

        if ($roleCount > 0) {
            return response()->json([
                'error' => "Cannot delete permission that is assigned to {$roleCount} role(s)."
            ], 422);
        }

        $permission->delete();

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully!'
        ]);
    }

    /**
     * Bulk delete permissions.
     * Requires 'role.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('role.delete')) {
            return response()->json(['error' => 'You do not have permission to delete permissions.'], 403);
        }

        $request->validate([
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        // Check if any permissions are assigned to roles
        $assignedPermissions = Permission::whereIn('id', $request->permission_ids)
            ->whereHas('roles')
            ->pluck('name')
            ->toArray();

        if (!empty($assignedPermissions)) {
            return response()->json([
                'error' => "Cannot delete permissions that are assigned to roles: " . implode(', ', $assignedPermissions)
            ], 422);
        }

        $deletedCount = Permission::whereIn('id', $request->permission_ids)->delete();

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} permission(s) deleted successfully!"
        ]);
    }

    /**
     * Get permissions grouped for assignment.
     * Requires 'role.view' permission.
     */
    public function grouped()
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view permissions.'], 403);
        }

        $permissions = Permission::all()->groupBy('group_name');
        
        return response()->json($permissions);
    }

    /**
     * Get permissions for a specific role.
     * Requires 'role.view' permission.
     */
    public function forRole($roleId)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view permissions.'], 403);
        }

        $role = Role::findOrFail($roleId);
        $permissions = Permission::all();
        
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return response()->json([
            'role' => $role->name,
            'all_permissions' => $permissions,
            'role_permissions' => $rolePermissions,
        ]);
    }

    /**
     * Sync permissions for a role.
     * Requires 'role.edit' permission.
     */
    public function syncRole(Request $request, $roleId)
    {
        if (!auth()->user()->can('role.edit')) {
            return response()->json(['error' => 'You do not have permission to update role permissions.'], 403);
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($roleId);
        $role->syncPermissions($request->permissions ?? []);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully!',
            'role' => $role->name,
            'assigned_permissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Get permission statistics.
     * Requires 'role.view' permission.
     */
    public function statistics()
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => Permission::count(),
            'by_group' => Permission::select('group_name', DB::raw('count(*) as total'))
                ->groupBy('group_name')
                ->pluck('total', 'group_name')
                ->toArray(),
            'assigned_count' => DB::table('role_has_permissions')->count(),
            'unassigned_count' => Permission::doesntHave('roles')->count(),
            'assigned_permissions' => Permission::has('roles')
                ->withCount('roles')
                ->orderBy('roles_count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Export permissions to CSV/Excel.
     * Requires 'role.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to export permissions.'], 403);
        }

        $permissions = Permission::all();

        // Prepare export data
        $exportData = [];
        $exportData[] = ['ID', 'Name', 'Group', 'Guard', 'Created At', 'Updated At'];

        foreach ($permissions as $permission) {
            $exportData[] = [
                $permission->id,
                $permission->name,
                $permission->group_name ?? 'Uncategorized',
                $permission->guard_name ?? 'web',
                $permission->created_at->format('Y-m-d H:i:s'),
                $permission->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $permissions->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Import permissions from CSV/Excel.
     * Requires 'role.create' permission.
     */
    public function import(Request $request)
    {
        if (!auth()->user()->can('role.create')) {
            return response()->json(['error' => 'You do not have permission to import permissions.'], 403);
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
     * Get dashboard widget data.
     */
    public function widget()
    {
        $total = Permission::count();
        $groups = Permission::select('group_name', DB::raw('count(*) as total'))
            ->groupBy('group_name')
            ->get();

        $assigned = Permission::has('roles')->count();
        $unassigned = Permission::doesntHave('roles')->count();

        return response()->json([
            'total' => $total,
            'groups' => $groups,
            'assigned' => $assigned,
            'unassigned' => $unassigned,
        ]);
    }

    /**
     * Check if a permission name exists.
     */
    public function checkName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'except_id' => 'nullable|exists:permissions,id',
        ]);

        $exists = Permission::where('name', $request->name)
            ->when($request->except_id, function($query) use ($request) {
                return $query->where('id', '!=', $request->except_id);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Permission name already exists' : 'Permission name is available',
        ]);
    }

    /**
     * Refresh permission cache.
     * Requires 'role.edit' permission.
     */
    public function refreshCache()
    {
        if (!auth()->user()->can('role.edit')) {
            return response()->json(['error' => 'You do not have permission to refresh cache.'], 403);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => 'Permission cache refreshed successfully!'
        ]);
    }

    /**
     * Get all available permission groups.
     */
    public function groups()
    {
        $groups = Permission::select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name')
            ->toArray();

        return response()->json($groups);
    }

    /**
     * Create default permission groups.
     * Requires 'role.create' permission.
     */
    public function createDefaultGroups()
    {
        if (!auth()->user()->can('role.create')) {
            return response()->json(['error' => 'You do not have permission to create default groups.'], 403);
        }

        $defaultPermissions = [
            'dashboard' => ['dashboard.view', 'dashboard.edit'],
            'user' => ['user.view', 'user.create', 'user.edit', 'user.delete', 'user.approve', 'user.suspend', 'user.reactivate'],
            'role' => ['role.view', 'role.create', 'role.edit', 'role.delete', 'role.assign_permissions'],
            'department' => ['department.view', 'department.create', 'department.edit', 'department.delete'],
            'category' => ['category.view', 'category.create', 'category.edit', 'category.delete'],
            'supplier' => ['supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete'],
            'product' => ['product.view', 'product.create', 'product.edit', 'product.delete', 'product.export', 'product.import'],
            'assignment' => ['assignment.view', 'assignment.create', 'assignment.edit', 'assignment.delete', 'assignment.return'],
            'stock' => ['stock.view', 'stock.create', 'stock.adjust'],
            'purchase_order' => ['purchase_order.view', 'purchase_order.create', 'purchase_order.edit', 'purchase_order.delete'],
            'maintenance' => ['maintenance.view', 'maintenance.create', 'maintenance.edit', 'maintenance.delete'],
            'asset_request' => ['asset_request.view', 'asset_request.create', 'asset_request.edit', 'asset_request.delete', 'asset_request.review', 'asset_request.approve', 'asset_request.reject'],
            'procurement' => ['procurement.view', 'procurement.create', 'procurement.edit', 'procurement.delete'],
            'inspection' => ['inspection.view', 'inspection.create', 'inspection.edit', 'inspection.pass', 'inspection.fail'],
            'direct_request' => ['direct_request.view', 'direct_request.create', 'direct_request.edit', 'direct_request.delete'],
            'notification' => ['notification.view', 'notification.create', 'notification.mark_read'],
            'report' => ['report.view', 'report.generate', 'report.export'],
            'settings' => ['settings.view', 'settings.edit', 'settings.manage'],
            'license' => ['license.view', 'license.create', 'license.edit', 'license.delete'],
            'activity_log' => ['activity_log.view', 'activity_log.export'],
            'api' => ['api.access'],
        ];

        $created = 0;
        $existing = 0;

        foreach ($defaultPermissions as $group => $permissions) {
            foreach ($permissions as $permName) {
                $exists = Permission::where('name', $permName)->exists();
                if (!$exists) {
                    Permission::create([
                        'name' => $permName,
                        'group_name' => $group,
                        'guard_name' => 'web',
                    ]);
                    $created++;
                } else {
                    $existing++;
                }
            }
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "{$created} default permissions created successfully! ({$existing} already existed)",
            'created_count' => $created,
            'existing_count' => $existing,
        ]);
    }

    /**
     * Get permissions by group.
     * Requires 'role.view' permission.
     */
    public function byGroup($group)
    {
        if (!auth()->user()->can('role.view')) {
            return response()->json(['error' => 'You do not have permission to view permissions.'], 403);
        }

        $permissions = Permission::where('group_name', $group)->get();

        return response()->json([
            'group' => $group,
            'count' => $permissions->count(),
            'permissions' => $permissions,
        ]);
    }
}
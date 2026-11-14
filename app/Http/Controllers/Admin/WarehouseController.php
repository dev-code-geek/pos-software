<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('warehouse.view')) {
            abort(403, 'You do not have permission to view warehouses.');
        }

        $warehouses = Warehouse::with('manager')->paginate(15);
        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('is_active', 1)->count(),
            'inactive' => Warehouse::where('is_active', 0)->count(),
            'with_products' => Warehouse::has('products')->count(),
        ];

        return view('admin.warehouses.index', compact('warehouses', 'stats'));
    }

    // No create() method – we use a modal

    public function store(Request $request)
    {
        if (!auth()->user()->can('warehouse.create')) {
            return response()->json(['error' => 'You do not have permission to create warehouses.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            Warehouse::create($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Warehouse created successfully!']);
            }
            return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse created successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to create warehouse: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to create warehouse.');
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('warehouse.view')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to view warehouses.'], 403);
            }
            abort(403);
        }

        try {
            $warehouse = Warehouse::with(['manager', 'products'])->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($warehouse);
            }
            return view('admin.warehouses.show', compact('warehouse'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Warehouse not found.'], 404);
            }
            abort(404);
        }
    }

    
     public function edit($id)
{
    if (!auth()->user()->can('warehouse.edit')) {
        if (request()->ajax()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
        abort(403);
    }

    $warehouse = Warehouse::find($id);

    if (!$warehouse) {
        if (request()->ajax()) {
            return response()->json(['error' => 'Warehouse not found.', 'id' => $id], 404);
        }
        abort(404);
    }

    $managers = User::role(['admin', 'warehouse_manager'])->get();

    if (request()->ajax()) {
        return response()->json([
            'warehouse' => $warehouse,
            'managers' => $managers,
        ]);
    }

    return redirect()->route('admin.warehouses.index');
}

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('warehouse.edit')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You do not have permission to edit warehouses.'], 403);
            }
            abort(403);
        }

        try {
            $warehouse = Warehouse::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:warehouses,code,' . $id,
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'manager_id' => 'nullable|exists:users,id',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $warehouse->update($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Warehouse updated successfully!']);
            }
            return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to update warehouse: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to update warehouse.');
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('warehouse.delete')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to delete warehouses.'], 403);
            }
            abort(403);
        }

        try {
            $warehouse = Warehouse::findOrFail($id);
            if ($warehouse->products()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json(['error' => 'Cannot delete warehouse with associated products.'], 422);
                }
                return redirect()->back()->with('error', 'Cannot delete warehouse with associated products.');
            }
            $warehouse->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Warehouse deleted successfully!']);
            }
            return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Failed to delete warehouse: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete warehouse.');
        }
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->can('warehouse.toggle_status')) {
            return response()->json(['error' => 'You do not have permission to toggle warehouse status.'], 403);
        }

        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->is_active = !$warehouse->is_active;
            $warehouse->save();
            return response()->json([
                'success' => true,
                'message' => 'Warehouse status toggled successfully!',
                'is_active' => $warehouse->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to toggle warehouse status: ' . $e->getMessage()], 500);
        }
    }

    public function dropdown()
    {
        if (!auth()->user()->can('warehouse.view')) {
            return response()->json(['error' => 'You do not have permission to view warehouses.'], 403);
        }

        $warehouses = Warehouse::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'code']);
        return response()->json($warehouses);
    }

    public function export()
    {
        if (!auth()->user()->can('warehouse.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }
}
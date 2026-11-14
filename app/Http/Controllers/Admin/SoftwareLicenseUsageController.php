<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SoftwareLicenseUsage;
use App\Models\Product;
use App\Models\User;
use App\Models\Facility;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SoftwareLicenseUsageController extends Controller
{
    /**
     * Display a listing of software license usage.
     * Requires 'license.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('license.view')) {
            abort(403, 'You do not have permission to view software licenses.');
        }

        $query = SoftwareLicenseUsage::with(['product', 'user', 'facility']);

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by assignee type
        if ($request->has('assigned_to_type') && $request->assigned_to_type) {
            $query->where('assigned_to_type', $request->assigned_to_type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by facility
        if ($request->has('facility_id') && $request->facility_id) {
            $query->where('facility_id', $request->facility_id);
        }

        // Filter by status (active/revoked)
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->whereNull('revoked_at');
            } elseif ($request->status === 'revoked') {
                $query->whereNotNull('revoked_at');
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('license_key', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('facility', function ($f) use ($search) {
                      $f->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $licenses = $query->latest('assigned_at')->paginate(15);

        // Stats
        $stats = [
            'total' => SoftwareLicenseUsage::count(),
            'active' => SoftwareLicenseUsage::whereNull('revoked_at')->count(),
            'revoked' => SoftwareLicenseUsage::whereNotNull('revoked_at')->count(),
            'assigned_to_user' => SoftwareLicenseUsage::where('assigned_to_type', 'user')->count(),
            'assigned_to_facility' => SoftwareLicenseUsage::where('assigned_to_type', 'facility')->count(),
            'today' => SoftwareLicenseUsage::whereDate('assigned_at', today())->count(),
            'this_month' => SoftwareLicenseUsage::whereMonth('assigned_at', now()->month)
                ->whereYear('assigned_at', now()->year)
                ->count(),
        ];

        $products = Product::where('type', 'software')->get();
        $users = User::all();
        $facilities = Facility::where('status', 'active')->get();

        return view('admin.software-licenses.index', compact('licenses', 'stats', 'products', 'users', 'facilities'));
    }

    /**
     * Show the form for creating a new license assignment.
     * Requires 'license.create' permission.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('license.create')) {
            abort(403, 'You do not have permission to assign software licenses.');
        }

        // Only show products with available licenses
        $products = Product::where('type', 'software')
            ->where('available_licenses', '>', 0)
            ->get();

        $users = User::all();
        $facilities = Facility::where('status', 'active')->get();

        $selectedProduct = null;
        if ($request->has('product_id')) {
            $selectedProduct = Product::find($request->product_id);
        }

        return view('admin.software-licenses.create', compact('products', 'users', 'facilities', 'selectedProduct'));
    }

    /**
     * Store a newly created license assignment.
     * Requires 'license.create' permission.
     *
     * IMPORTANT: $request->validate() is called OUTSIDE the try/catch below.
     * This lets Laravel's own exception handler catch ValidationException and
     * return the standard {message, errors} JSON shape with a 422 status,
     * which is exactly what the front-end's $.ajax error handler expects
     * (xhr.responseJSON.errors). Previously this was wrapped in a try/catch
     * that reformatted it into {error: "..."} (singular string), so the
     * front-end's `.errors` lookup was always undefined and the "Validation
     * Error!" popup rendered with an empty list.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('license.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to assign software licenses.',
            ], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'assigned_to_type' => 'required|in:user,facility',
            // 'nullable' is required here: required_if only makes the field
            // mandatory when assigned_to_type matches, it does NOT exempt the
            // field from exists:... when the opposite type is chosen. Without
            // 'nullable', an empty string submitted for the unused field gets
            // validated against exists:users,id / exists:facilities,id and
            // fails with "The selected user id is invalid." even when the
            // user correctly picked "Facility".
            'user_id' => 'nullable|required_if:assigned_to_type,user|exists:users,id',
            'facility_id' => 'nullable|required_if:assigned_to_type,facility|exists:facilities,id',
            'license_key' => 'required|string|max:255|unique:software_license_usage,license_key',
            'assigned_at' => 'required|date',
        ]);

        try {
            // Find the product
            $product = Product::findOrFail($validated['product_id']);

            // Check if product is software
            if ($product->type !== 'software') {
                return response()->json([
                    'success' => false,
                    'error' => 'Product is not software. Please select a software product.',
                ], 422);
            }

            // Check if product has available licenses
            if ($product->available_licenses <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No available licenses for this software. Available: 0',
                ], 422);
            }

            // Check if assignee already has this license
            $existingLicense = SoftwareLicenseUsage::where('product_id', $validated['product_id'])
                ->whereNull('revoked_at');

            if ($validated['assigned_to_type'] === 'user') {
                $existingLicense->where('user_id', $validated['user_id']);
            } else {
                $existingLicense->where('facility_id', $validated['facility_id']);
            }

            if ($existingLicense->exists()) {
                $assigneeName = $validated['assigned_to_type'] === 'user'
                    ? User::find($validated['user_id'])->name
                    : Facility::find($validated['facility_id'])->name;

                return response()->json([
                    'success' => false,
                    'error' => "This assignee ({$assigneeName}) already has an active license for this software.",
                ], 422);
            }

            // Prepare data
            $licenseData = [
                'product_id' => $validated['product_id'],
                'assigned_to_type' => $validated['assigned_to_type'],
                'license_key' => $validated['license_key'],
                'assigned_at' => $validated['assigned_at'],
            ];

            // Set user_id or facility_id based on type
            if ($validated['assigned_to_type'] === 'user') {
                $licenseData['user_id'] = $validated['user_id'];
                $licenseData['facility_id'] = null;
            } else {
                $licenseData['facility_id'] = $validated['facility_id'];
                $licenseData['user_id'] = null;
            }

            // Create license usage
            $license = SoftwareLicenseUsage::create($licenseData);

            // Decrement available licenses
            $product->available_licenses -= 1;
            $product->quantity -= 1;
            if ($product->available_licenses == 0) {
                $product->status = 'assigned';
            }
            $product->save();

            // Notify the assignee
            $this->sendNotification($license, $product, 'assigned');

            return response()->json([
                'success' => true,
                'message' => 'License assigned successfully!',
                'license' => $license,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('License assignment failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to assign license: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified license usage.
     * Requires 'license.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('license.view')) {
            return response()->json(['error' => 'You do not have permission to view software licenses.'], 403);
        }

        $license = SoftwareLicenseUsage::with(['product', 'user', 'facility'])->findOrFail($id);

        return response()->json($license);
    }

    /**
     * Show the form for editing the specified license usage.
     * Requires 'license.edit' permission.
     */
     /**
 * Show the form for editing the specified license usage.
 * Requires 'license.edit' permission.
 */
public function edit($id)
{
    if (!auth()->user()->can('license.edit')) {
        return response()->json([
            'success' => false,
            'error' => 'You do not have permission to edit software licenses.'
        ], 403);
    }

    try {
        $license = SoftwareLicenseUsage::with(['product', 'user', 'facility'])->findOrFail($id);
        
        // Log the license data for debugging
        \Log::info('License edit data:', [
            'id' => $license->id,
            'product_id' => $license->product_id,
            'user_id' => $license->user_id,
            'facility_id' => $license->facility_id,
            'assigned_to_type' => $license->assigned_to_type,
            'license_key' => $license->license_key,
        ]);

        // If this is an AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($license);
        }

        // For normal view
        $products = Product::where('type', 'software')->get();
        $users = User::all();
        $facilities = Facility::where('status', 'active')->get();

        return view('admin.software-licenses.edit', compact('license', 'products', 'users', 'facilities'));
        
    } catch (\Exception $e) {
        \Log::error('Edit license failed: ' . $e->getMessage(), [
            'id' => $id,
            'trace' => $e->getTraceAsString()
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load license data: ' . $e->getMessage()
            ], 500);
        }

        abort(500, 'Failed to load license data.');
    }
}
    /**
     * Update the specified license usage.
     * Requires 'license.edit' permission.
     *
     * Same fix as store(): validation happens outside the try/catch so
     * ValidationException reaches Laravel's default handler and produces
     * a proper {message, errors} 422 response for the front-end.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('license.edit')) {
            return response()->json(['error' => 'You do not have permission to edit software licenses.'], 403);
        }

        $license = SoftwareLicenseUsage::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'assigned_to_type' => 'required|in:user,facility',
            // Same fix as store(): nullable prevents exists:... from running
            // against an empty string on the unused field.
            'user_id' => 'nullable|required_if:assigned_to_type,user|exists:users,id',
            'facility_id' => 'nullable|required_if:assigned_to_type,facility|exists:facilities,id',
            'license_key' => 'required|string|max:255|unique:software_license_usage,license_key,' . $id,
            'assigned_at' => 'required|date',
        ]);

        try {
            // Prepare data
            $licenseData = [
                'product_id' => $validated['product_id'],
                'assigned_to_type' => $validated['assigned_to_type'],
                'license_key' => $validated['license_key'],
                'assigned_at' => $validated['assigned_at'],
            ];

            // Set user_id or facility_id based on type
            if ($validated['assigned_to_type'] === 'user') {
                $licenseData['user_id'] = $validated['user_id'];
                $licenseData['facility_id'] = null;
            } else {
                $licenseData['facility_id'] = $validated['facility_id'];
                $licenseData['user_id'] = null;
            }

            $license->update($licenseData);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'License updated successfully!',
                    'license' => $license,
                ]);
            }

            return redirect()->route('admin.software-licenses.index')
                ->with('success', 'License updated successfully!');
        } catch (\Exception $e) {
            Log::error('License update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update license: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified license usage.
     * Requires 'license.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('license.delete')) {
            return response()->json(['error' => 'You do not have permission to delete software licenses.'], 403);
        }

        try {
            $license = SoftwareLicenseUsage::findOrFail($id);

            // If license is active, revoke it first
            if (is_null($license->revoked_at)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete an active license. Revoke it first.',
                ], 422);
            }

            $license->delete();

            return response()->json([
                'success' => true,
                'message' => 'License deleted successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('License deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete license: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revoke a license.
     * Requires 'license.revoke' permission.
     *
     * Same fix: validation moved outside the try/catch.
     */
    public function revoke(Request $request, $id)
    {
        if (!auth()->user()->can('license.revoke')) {
            return response()->json(['error' => 'You do not have permission to revoke licenses.'], 403);
        }

        $license = SoftwareLicenseUsage::findOrFail($id);

        if (!is_null($license->revoked_at)) {
            return response()->json([
                'success' => false,
                'error' => 'License is already revoked.',
            ], 422);
        }

        $validated = $request->validate([
            'revoked_at' => 'required|date',
            'revoke_reason' => 'nullable|string|max:255',
        ]);

        try {
            $license->update([
                'revoked_at' => $validated['revoked_at'],
            ]);

            // Update product available licenses
            $product = $license->product;
            $product->available_licenses += 1;
            $product->quantity += 1;
            if ($product->status === 'assigned' && $product->available_licenses > 0) {
                $product->status = 'available';
            }
            $product->save();

            // Notify the assignee
            $this->sendNotification($license, $product, 'revoked');

            return response()->json([
                'success' => true,
                'message' => 'License revoked successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('License revocation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to revoke license: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk revoke licenses.
     * Requires 'license.revoke' permission.
     *
     * Same fix: validation moved outside the try/catch.
     */
    public function bulkRevoke(Request $request)
    {
        if (!auth()->user()->can('license.revoke')) {
            return response()->json(['error' => 'You do not have permission to revoke licenses.'], 403);
        }

        $validated = $request->validate([
            'license_ids' => 'required|array|min:1',
            'license_ids.*' => 'exists:software_license_usage,id',
            'revoked_at' => 'required|date',
        ]);

        try {
            $licenses = SoftwareLicenseUsage::whereIn('id', $validated['license_ids'])
                ->whereNull('revoked_at')
                ->get();

            if ($licenses->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active licenses found to revoke.',
                ], 422);
            }

            foreach ($licenses as $license) {
                $license->update(['revoked_at' => $validated['revoked_at']]);

                // Update product available licenses
                $product = $license->product;
                $product->available_licenses += 1;
                $product->quantity += 1;
                if ($product->status === 'assigned' && $product->available_licenses > 0) {
                    $product->status = 'available';
                }
                $product->save();
            }

            return response()->json([
                'success' => true,
                'message' => count($licenses) . ' license(s) revoked successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk license revocation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to revoke licenses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get licenses for a specific user.
     * Requires 'license.view' permission.
     */
    public function byUser($userId)
    {
        if (!auth()->user()->can('license.view')) {
            return response()->json(['error' => 'You do not have permission to view software licenses.'], 403);
        }

        $licenses = SoftwareLicenseUsage::with('product')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $user = User::find($userId);

        return response()->json([
            'user' => $user->name ?? 'Unknown',
            'total' => $licenses->count(),
            'active' => $licenses->whereNull('revoked_at')->count(),
            'revoked' => $licenses->whereNotNull('revoked_at')->count(),
            'licenses' => $licenses,
        ]);
    }

    /**
     * Get licenses for a specific facility.
     * Requires 'license.view' permission.
     */
    public function byFacility($facilityId)
    {
        if (!auth()->user()->can('license.view')) {
            return response()->json(['error' => 'You do not have permission to view software licenses.'], 403);
        }

        $licenses = SoftwareLicenseUsage::with('product')
            ->where('facility_id', $facilityId)
            ->latest()
            ->get();

        $facility = Facility::find($facilityId);

        return response()->json([
            'facility' => $facility->name ?? 'Unknown',
            'total' => $licenses->count(),
            'active' => $licenses->whereNull('revoked_at')->count(),
            'revoked' => $licenses->whereNotNull('revoked_at')->count(),
            'licenses' => $licenses,
        ]);
    }

    /**
     * Get licenses for a specific product.
     * Requires 'license.view' permission.
     */
    public function byProduct($productId)
    {
        if (!auth()->user()->can('license.view')) {
            return response()->json(['error' => 'You do not have permission to view software licenses.'], 403);
        }

        $licenses = SoftwareLicenseUsage::with(['user', 'facility'])
            ->where('product_id', $productId)
            ->latest()
            ->get();

        $product = Product::find($productId);

        return response()->json([
            'product' => $product->name ?? 'Unknown',
            'total' => $licenses->count(),
            'active' => $licenses->whereNull('revoked_at')->count(),
            'revoked' => $licenses->whereNotNull('revoked_at')->count(),
            'assigned_to_users' => $licenses->where('assigned_to_type', 'user')->count(),
            'assigned_to_facilities' => $licenses->where('assigned_to_type', 'facility')->count(),
            'licenses' => $licenses,
        ]);
    }

    /**
     * Get license statistics.
     * Requires 'license.view' permission.
     */
    public function statistics(Request $request)
    {
        if (!auth()->user()->can('license.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $stats = [
            'total' => SoftwareLicenseUsage::count(),
            'active' => SoftwareLicenseUsage::whereNull('revoked_at')->count(),
            'revoked' => SoftwareLicenseUsage::whereNotNull('revoked_at')->count(),
            'assigned_to_user' => SoftwareLicenseUsage::where('assigned_to_type', 'user')->count(),
            'assigned_to_facility' => SoftwareLicenseUsage::where('assigned_to_type', 'facility')->count(),
            'by_user' => SoftwareLicenseUsage::with('user')
                ->where('assigned_to_type', 'user')
                ->select('user_id', DB::raw('count(*) as total'), DB::raw('sum(case when revoked_at is null then 1 else 0 end) as active'))
                ->groupBy('user_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'by_facility' => SoftwareLicenseUsage::with('facility')
                ->where('assigned_to_type', 'facility')
                ->select('facility_id', DB::raw('count(*) as total'), DB::raw('sum(case when revoked_at is null then 1 else 0 end) as active'))
                ->groupBy('facility_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'by_product' => SoftwareLicenseUsage::with('product')
                ->select('product_id', DB::raw('count(*) as total'), DB::raw('sum(case when revoked_at is null then 1 else 0 end) as active'))
                ->groupBy('product_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'today_assigned' => SoftwareLicenseUsage::whereDate('assigned_at', today())->count(),
            'today_revoked' => SoftwareLicenseUsage::whereDate('revoked_at', today())->count(),
            'this_month' => SoftwareLicenseUsage::whereMonth('assigned_at', now()->month)
                ->whereYear('assigned_at', now()->year)
                ->count(),
            'expiring_soon' => Product::where('type', 'software')
                ->where('license_expiry_date', '<=', now()->addDays(30))
                ->where('license_expiry_date', '>=', now())
                ->count(),
            'expired' => Product::where('type', 'software')
                ->where('license_expiry_date', '<', now())
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentAssignments = SoftwareLicenseUsage::with(['product', 'user', 'facility'])
            ->latest('assigned_at')
            ->limit(5)
            ->get();

        $activeCount = SoftwareLicenseUsage::whereNull('revoked_at')->count();
        $totalCount = SoftwareLicenseUsage::count();

        // Get expiring licenses
        $expiringLicenses = Product::where('type', 'software')
            ->where('license_expiry_date', '<=', now()->addDays(30))
            ->where('license_expiry_date', '>=', now())
            ->get(['id', 'name', 'license_expiry_date']);

        return response()->json([
            'recent_assignments' => $recentAssignments,
            'active_licenses' => $activeCount,
            'total_licenses' => $totalCount,
            'expiring_licenses' => $expiringLicenses,
            'expiring_count' => $expiringLicenses->count(),
            'assigned_today' => SoftwareLicenseUsage::whereDate('assigned_at', today())->count(),
            'revoked_today' => SoftwareLicenseUsage::whereDate('revoked_at', today())->count(),
        ]);
    }

    /**
     * Export licenses to CSV/Excel.
     * Requires 'license.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('license.view')) {
            return response()->json(['error' => 'You do not have permission to export licenses.'], 403);
        }

        $licenses = SoftwareLicenseUsage::with(['product', 'user', 'facility'])
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'Product', 'Assigned To', 'Assignee Type', 'License Key',
            'Assigned Date', 'Revoked Date', 'Status', 'Created At',
        ];

        foreach ($licenses as $license) {
            $exportData[] = [
                $license->id,
                $license->product->name ?? 'N/A',
                $license->assignee_name,
                $license->assignee_type_label,
                $license->license_key,
                $license->assigned_at->format('Y-m-d'),
                $license->revoked_at ? $license->revoked_at->format('Y-m-d') : 'Active',
                $license->revoked_at ? 'Revoked' : 'Active',
                $license->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $licenses->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get available licenses for a product.
     * Requires 'license.view' permission.
     */
    public function available($productId)
    {
        if (!auth()->user()->can('license.view')) {
            return response()->json(['error' => 'You do not have permission to view licenses.'], 403);
        }

        $product = Product::findOrFail($productId);

        if ($product->type !== 'software') {
            return response()->json(['error' => 'Product is not software.'], 422);
        }

        $assignedLicenses = SoftwareLicenseUsage::where('product_id', $productId)
            ->whereNull('revoked_at')
            ->get();

        return response()->json([
            'product' => $product->name,
            'total_licenses' => $product->max_licenses ?? $product->quantity,
            'available_licenses' => $product->available_licenses,
            'assigned_licenses' => $assignedLicenses->count(),
            'assigned_to_users' => $assignedLicenses->where('assigned_to_type', 'user')->count(),
            'assigned_to_facilities' => $assignedLicenses->where('assigned_to_type', 'facility')->count(),
            'assigned_list' => $assignedLicenses->map(function ($license) {
                return [
                    'assignee' => $license->assignee_name,
                    'assignee_type' => $license->assignee_type_label,
                    'license_key' => $license->license_key,
                    'assigned_at' => $license->assigned_at->format('Y-m-d'),
                ];
            }),
        ]);
    }

    /**
     * Get my licenses (for current user).
     */
    public function myLicenses()
    {
        $userId = auth()->id();

        $activeLicenses = SoftwareLicenseUsage::with('product')
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get();

        $revokedLicenses = SoftwareLicenseUsage::with('product')
            ->where('user_id', $userId)
            ->whereNotNull('revoked_at')
            ->latest()
            ->get();

        return response()->json([
            'active' => $activeLicenses,
            'active_count' => $activeLicenses->count(),
            'revoked' => $revokedLicenses,
            'revoked_count' => $revokedLicenses->count(),
            'total' => $activeLicenses->count() + $revokedLicenses->count(),
        ]);
    }

    /**
     * Send notification for license assignment/revocation.
     */
    private function sendNotification($license, $product, $action)
    {
        $userId = null;

        if ($license->assigned_to_type === 'user') {
            $userId = $license->user_id;
        } else {
            // For facilities, notify the manager if available
            $facility = $license->facility;
            $userId = $facility->manager_id ?? null;
        }

        if ($userId) {
            $message = $action === 'assigned'
                ? "You have been assigned a license for {$product->name} (License Key: {$license->license_key})"
                : "Your license for {$product->name} has been revoked.";

            Notification::create([
                'user_id' => $userId,
                'type' => 'assignment',
                'message' => $message,
                'link' => route('user.my-assignments'),
            ]);
        }
    }
}
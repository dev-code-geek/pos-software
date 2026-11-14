<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    /**
     * Display a listing of facilities.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('facility.view')) {
            abort(403, 'You do not have permission to view facilities.');
        }

        $query = Facility::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by city
        if ($request->has('city') && $request->city) {
            $query->where('city', $request->city);
        }

        // Filter by verified
        if ($request->has('verified') && $request->verified) {
            if ($request->verified === 'yes') {
                $query->where('is_verified', true);
            } elseif ($request->verified === 'no') {
                $query->where('is_verified', false);
            }
        }

        $perPage = $request->per_page ?? 15;
        $facilities = $query->latest()->paginate($perPage);

        // Stats
        $stats = [
            'total' => Facility::count(),
            'active' => Facility::where('status', 'active')->count(),
            'inactive' => Facility::where('status', 'inactive')->count(),
            'pending' => Facility::where('status', 'pending')->count(),
            'verified' => Facility::where('is_verified', true)->count(),
            'hospitals' => Facility::where('type', 'hospital')->count(),
            'schools' => Facility::where('type', 'school')->count(),
            'offices' => Facility::where('type', 'office')->count(),
        ];

        $types = ['hospital', 'school', 'office', 'warehouse', 'other'];
        $statuses = ['active', 'inactive', 'pending', 'suspended'];

        return view('admin.facilities.index', compact('facilities', 'stats', 'types', 'statuses'));
    }

    /**
     * Show the form for creating a new facility.
     */
    public function create()
    {
        if (!auth()->user()->can('facility.create')) {
            abort(403, 'You do not have permission to create facilities.');
        }

        $types = ['hospital', 'school', 'office', 'warehouse', 'other'];
        $statuses = ['active', 'inactive', 'pending', 'suspended'];

        return view('admin.facilities.create', compact('types', 'statuses'));
    }

    /**
     * Store a newly created facility.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('facility.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create facilities.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:hospital,school,office,warehouse,other',
                'category' => 'nullable|string|max:100',
                'sub_category' => 'nullable|string|max:100',
                'code' => 'nullable|string|max:50|unique:facilities',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'contact_person' => 'nullable|string|max:255',
                'contact_phone' => 'nullable|string|max:50',
                'contact_email' => 'nullable|email|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive,pending,suspended',
                'notes' => 'nullable|string',
            ]);

            $validated['created_by'] = auth()->id();
            $validated['is_verified'] = $request->has('is_verified');

            if ($validated['is_verified']) {
                $validated['verified_at'] = now();
                $validated['verified_by'] = auth()->id();
            }

            // Generate code if not provided
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateCode($validated['type'], $validated['name']);
            }

            $facility = Facility::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Facility created successfully!',
                    'facility' => $facility
                ]);
            }

            return redirect()
                ->route('admin.facilities.index')
                ->with('success', 'Facility created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating facility: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create facility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified facility.
     */
    public function show($id)
    {
        try {
            if (!auth()->user()->can('facility.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view facilities.'
                ], 403);
            }

            $facility = Facility::with(['createdBy', 'verifiedBy', 'products'])
                ->findOrFail($id);

            $productStats = [
                'total' => $facility->products->count(),
                'hardware' => $facility->products->where('type', 'hardware')->count(),
                'software' => $facility->products->where('type', 'software')->count(),
                'available' => $facility->products->where('status', 'available')->count(),
                'assigned' => $facility->products->where('status', 'assigned')->count(),
            ];

            return response()->json([
                'success' => true,
                'facility' => $facility,
                'product_stats' => $productStats,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching facility: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load facility details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified facility.
     * RETURNS JSON FOR AJAX
     */
    public function edit($id)
    {
        try {
            if (!auth()->user()->can('facility.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit facilities.'
                ], 403);
            }

            $facility = Facility::findOrFail($id);

            // Format the facility data for the edit form
            $facilityData = [
                'id' => $facility->id,
                'name' => $facility->name,
                'type' => $facility->type,
                'category' => $facility->category,
                'sub_category' => $facility->sub_category,
                'code' => $facility->code,
                'status' => $facility->status,
                'is_verified' => $facility->is_verified,
                'email' => $facility->email,
                'phone' => $facility->phone,
                'website' => $facility->website,
                'contact_person' => $facility->contact_person,
                'contact_phone' => $facility->contact_phone,
                'contact_email' => $facility->contact_email,
                'address' => $facility->address,
                'city' => $facility->city,
                'state' => $facility->state,
                'country' => $facility->country,
                'postal_code' => $facility->postal_code,
                'description' => $facility->description,
                'notes' => $facility->notes,
                'created_at' => $facility->created_at,
                'updated_at' => $facility->updated_at,
            ];

            return response()->json([
                'success' => true,
                'facility' => $facilityData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching facility for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load facility data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified facility.
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('facility.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit facilities.'
                ], 403);
            }

            $facility = Facility::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:hospital,school,office,warehouse,other',
                'category' => 'nullable|string|max:100',
                'sub_category' => 'nullable|string|max:100',
                'code' => 'nullable|string|max:50|unique:facilities,code,' . $id,
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'contact_person' => 'nullable|string|max:255',
                'contact_phone' => 'nullable|string|max:50',
                'contact_email' => 'nullable|email|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive,pending,suspended',
                'notes' => 'nullable|string',
            ]);

            $validated['updated_by'] = auth()->id();

            // Handle verification
            if ($request->has('is_verified') && !$facility->is_verified) {
                $validated['is_verified'] = true;
                $validated['verified_at'] = now();
                $validated['verified_by'] = auth()->id();
            } elseif (!$request->has('is_verified')) {
                $validated['is_verified'] = false;
                $validated['verified_at'] = null;
                $validated['verified_by'] = null;
            }

            $facility->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Facility updated successfully!',
                    'facility' => $facility
                ]);
            }

            return redirect()
                ->route('admin.facilities.index')
                ->with('success', 'Facility updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating facility: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update facility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified facility.
     */
    public function destroy($id)
    {
        try {
            if (!auth()->user()->can('facility.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete facilities.'
                ], 403);
            }

            $facility = Facility::findOrFail($id);

            // Check if facility has products
            if ($facility->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete facility with associated products. Reassign products first.'
                ], 422);
            }

            $facility->delete();

            return response()->json([
                'success' => true,
                'message' => 'Facility deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting facility: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete facility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify a facility.
     */
    public function verify($id)
    {
        try {
            if (!auth()->user()->can('facility.verify')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to verify facilities.'
                ], 403);
            }

            $facility = Facility::findOrFail($id);
            $facility->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Facility verified successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error verifying facility: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify facility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique code for the facility.
     */
    private function generateCode($type, $name)
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $namePart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4));
        $random = strtoupper(substr(uniqid(), -4));
        $code = $prefix . '-' . $namePart . '-' . $random;

        // Ensure uniqueness
        while (Facility::where('code', $code)->exists()) {
            $random = strtoupper(substr(uniqid(), -4));
            $code = $prefix . '-' . $namePart . '-' . $random;
        }

        return $code;
    }

    /**
     * Get facilities dropdown for select inputs.
     */
    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('facility.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view facilities.'
            ], 403);
        }

        try {
            $query = Facility::select('id', 'name', 'type', 'code');

            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%");
                });
            }

            $facilities = $query->orderBy('name')->limit(50)->get();

            return response()->json([
                'success' => true,
                'facilities' => $facilities
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching facility dropdown: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch facilities.'
            ], 500);
        }
    }

    /**
     * Export facilities to CSV/Excel.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('facility.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to export facilities.'
            ], 403);
        }

        try {
            $facilities = Facility::all();

            $exportData = [];
            $exportData[] = ['ID', 'Name', 'Type', 'Code', 'Email', 'Phone', 'Contact Person', 'City', 'Country', 'Status', 'Verified', 'Created At'];

            foreach ($facilities as $facility) {
                $exportData[] = [
                    $facility->id,
                    $facility->name,
                    $facility->type_label ?? $facility->type,
                    $facility->code ?? 'N/A',
                    $facility->email ?? 'N/A',
                    $facility->phone ?? 'N/A',
                    $facility->contact_person ?? 'N/A',
                    $facility->city ?? 'N/A',
                    $facility->country ?? 'N/A',
                    ucfirst($facility->status),
                    $facility->is_verified ? 'Yes' : 'No',
                    $facility->created_at->format('Y-m-d H:i:s'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'count' => $facilities->count(),
                'exported_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting facilities: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export facilities.'
            ], 500);
        }
    }

    /**
     * Get facility statistics.
     */
    public function statistics()
    {
        if (!auth()->user()->can('facility.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view statistics.'
            ], 403);
        }

        try {
            $stats = [
                'total' => Facility::count(),
                'by_type' => Facility::select('type', DB::raw('count(*) as total'))
                    ->groupBy('type')
                    ->pluck('total', 'type')
                    ->toArray(),
                'by_status' => Facility::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray(),
                'verified' => Facility::where('is_verified', true)->count(),
                'unverified' => Facility::where('is_verified', false)->count(),
                'with_products' => Facility::has('products')->count(),
                'without_products' => Facility::doesntHave('products')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching facility statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Get facilities by type.
     */
    public function byType($type)
    {
        if (!auth()->user()->can('facility.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view facilities.'
            ], 403);
        }

        try {
            $facilities = Facility::where('type', $type)->get();

            return response()->json([
                'success' => true,
                'type' => $type,
                'count' => $facilities->count(),
                'facilities' => $facilities
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching facilities by type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch facilities.'
            ], 500);
        }
    }
}
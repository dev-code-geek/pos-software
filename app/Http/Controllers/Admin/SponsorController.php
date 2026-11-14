<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SponsorController extends Controller
{
    /**
     * Display a listing of sponsors.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('sponsor.view')) {
            abort(403, 'You do not have permission to view sponsors.');
        }

        $query = Sponsor::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('registration_number', 'LIKE', "%{$search}%");
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

        // Filter by donation type
        if ($request->has('donation_type') && $request->donation_type) {
            $query->where('donation_type', $request->donation_type);
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
        $sponsors = $query->latest()->paginate($perPage);

        // Stats
        $stats = [
            'total' => Sponsor::count(),
            'active' => Sponsor::where('status', 'active')->count(),
            'inactive' => Sponsor::where('status', 'inactive')->count(),
            'pending' => Sponsor::where('status', 'pending')->count(),
            'verified' => Sponsor::where('is_verified', true)->count(),
            'government' => Sponsor::where('type', 'government')->count(),
            'ngo' => Sponsor::where('type', 'ngo')->count(),
            'corporate' => Sponsor::where('type', 'corporate')->count(),
            'total_donations' => Sponsor::sum('total_donations'),
        ];

        $types = ['government', 'ngo', 'corporate', 'individual', 'foundation', 'other'];
        $statuses = ['active', 'inactive', 'pending', 'suspended'];
        $donationTypes = ['financial', 'equipment', 'services', 'training', 'other'];

        return view('admin.sponsors.index', compact('sponsors', 'stats', 'types', 'statuses', 'donationTypes'));
    }

    /**
     * Show the form for creating a new sponsor.
     */
    public function create()
    {
        if (!auth()->user()->can('sponsor.create')) {
            abort(403, 'You do not have permission to create sponsors.');
        }

        $types = ['government', 'ngo', 'corporate', 'individual', 'foundation', 'other'];
        $statuses = ['active', 'inactive', 'pending', 'suspended'];
        $donationTypes = ['financial', 'equipment', 'services', 'training', 'other'];

        return view('admin.sponsors.create', compact('types', 'statuses', 'donationTypes'));
    }

    /**
     * Store a newly created sponsor.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('sponsor.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create sponsors.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:government,ngo,corporate,individual,foundation,other',
                'category' => 'nullable|string|max:100',
                'code' => 'nullable|string|max:50|unique:sponsors',
                'registration_number' => 'nullable|string|max:100|unique:sponsors',
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
                'donation_type' => 'nullable|in:financial,equipment,services,training,other',
                'total_donations' => 'nullable|numeric|min:0',
                'donation_currency' => 'nullable|string|max:10',
                'last_donation_date' => 'nullable|date',
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

            $sponsor = Sponsor::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sponsor created successfully!',
                    'sponsor' => $sponsor
                ]);
            }

            return redirect()
                ->route('admin.sponsors.index')
                ->with('success', 'Sponsor created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating sponsor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sponsor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sponsor.
     */
    public function show($id)
    {
        try {
            if (!auth()->user()->can('sponsor.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view sponsors.'
                ], 403);
            }

            $sponsor = Sponsor::with(['createdBy', 'verifiedBy', 'products'])
                ->findOrFail($id);

            $productStats = [
                'total' => $sponsor->products->count(),
                'hardware' => $sponsor->products->where('type', 'hardware')->count(),
                'software' => $sponsor->products->where('type', 'software')->count(),
                'available' => $sponsor->products->where('status', 'available')->count(),
                'assigned' => $sponsor->products->where('status', 'assigned')->count(),
            ];

            return response()->json([
                'success' => true,
                'sponsor' => $sponsor,
                'product_stats' => $productStats,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching sponsor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load sponsor details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified sponsor.
     * RETURNS JSON FOR AJAX
     */
    public function edit($id)
    {
        try {
            if (!auth()->user()->can('sponsor.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit sponsors.'
                ], 403);
            }

            $sponsor = Sponsor::findOrFail($id);

            // Format the sponsor data for the edit form
            $sponsorData = [
                'id' => $sponsor->id,
                'name' => $sponsor->name,
                'type' => $sponsor->type,
                'category' => $sponsor->category,
                'code' => $sponsor->code,
                'status' => $sponsor->status,
                'is_verified' => $sponsor->is_verified,
                'registration_number' => $sponsor->registration_number,
                'donation_type' => $sponsor->donation_type,
                'donation_currency' => $sponsor->donation_currency,
                'total_donations' => $sponsor->total_donations,
                'last_donation_date' => $sponsor->last_donation_date,
                'email' => $sponsor->email,
                'phone' => $sponsor->phone,
                'website' => $sponsor->website,
                'contact_person' => $sponsor->contact_person,
                'contact_phone' => $sponsor->contact_phone,
                'contact_email' => $sponsor->contact_email,
                'address' => $sponsor->address,
                'city' => $sponsor->city,
                'state' => $sponsor->state,
                'country' => $sponsor->country,
                'postal_code' => $sponsor->postal_code,
                'description' => $sponsor->description,
                'notes' => $sponsor->notes,
                'created_at' => $sponsor->created_at,
                'updated_at' => $sponsor->updated_at,
            ];

            return response()->json([
                'success' => true,
                'sponsor' => $sponsorData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching sponsor for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load sponsor data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified sponsor.
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('sponsor.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit sponsors.'
                ], 403);
            }

            $sponsor = Sponsor::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:government,ngo,corporate,individual,foundation,other',
                'category' => 'nullable|string|max:100',
                'code' => 'nullable|string|max:50|unique:sponsors,code,' . $id,
                'registration_number' => 'nullable|string|max:100|unique:sponsors,registration_number,' . $id,
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
                'donation_type' => 'nullable|in:financial,equipment,services,training,other',
                'total_donations' => 'nullable|numeric|min:0',
                'donation_currency' => 'nullable|string|max:10',
                'last_donation_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            $validated['updated_by'] = auth()->id();

            // Handle verification
            if ($request->has('is_verified') && !$sponsor->is_verified) {
                $validated['is_verified'] = true;
                $validated['verified_at'] = now();
                $validated['verified_by'] = auth()->id();
            } elseif (!$request->has('is_verified')) {
                $validated['is_verified'] = false;
                $validated['verified_at'] = null;
                $validated['verified_by'] = null;
            }

            $sponsor->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sponsor updated successfully!',
                    'sponsor' => $sponsor
                ]);
            }

            return redirect()
                ->route('admin.sponsors.index')
                ->with('success', 'Sponsor updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating sponsor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sponsor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified sponsor.
     */
    public function destroy($id)
    {
        try {
            if (!auth()->user()->can('sponsor.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete sponsors.'
                ], 403);
            }

            $sponsor = Sponsor::findOrFail($id);

            // Check if sponsor has products
            if ($sponsor->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete sponsor with associated products. Reassign products first.'
                ], 422);
            }

            $sponsor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sponsor deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting sponsor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sponsor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify a sponsor.
     */
    public function verify($id)
    {
        try {
            if (!auth()->user()->can('sponsor.verify')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to verify sponsors.'
                ], 403);
            }

            $sponsor = Sponsor::findOrFail($id);
            $sponsor->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sponsor verified successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error verifying sponsor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify sponsor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique code for the sponsor.
     */
    private function generateCode($type, $name)
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $namePart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4));
        $random = strtoupper(substr(uniqid(), -4));
        $code = $prefix . '-' . $namePart . '-' . $random;

        while (Sponsor::where('code', $code)->exists()) {
            $random = strtoupper(substr(uniqid(), -4));
            $code = $prefix . '-' . $namePart . '-' . $random;
        }

        return $code;
    }

    /**
     * Get sponsors dropdown for select inputs.
     */
    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('sponsor.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view sponsors.'
            ], 403);
        }

        try {
            $query = Sponsor::select('id', 'name', 'type', 'code');

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

            $sponsors = $query->orderBy('name')->limit(50)->get();

            return response()->json([
                'success' => true,
                'sponsors' => $sponsors
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching sponsor dropdown: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sponsors.'
            ], 500);
        }
    }

    /**
     * Export sponsors to CSV/Excel.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('sponsor.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to export sponsors.'
            ], 403);
        }

        try {
            $sponsors = Sponsor::all();

            $exportData = [];
            $exportData[] = ['ID', 'Name', 'Type', 'Code', 'Email', 'Phone', 'Contact Person', 'City', 'Country', 'Status', 'Verified', 'Donation Type', 'Total Donations', 'Created At'];

            foreach ($sponsors as $sponsor) {
                $exportData[] = [
                    $sponsor->id,
                    $sponsor->name,
                    $sponsor->type_label ?? $sponsor->type,
                    $sponsor->code ?? 'N/A',
                    $sponsor->email ?? 'N/A',
                    $sponsor->phone ?? 'N/A',
                    $sponsor->contact_person ?? 'N/A',
                    $sponsor->city ?? 'N/A',
                    $sponsor->country ?? 'N/A',
                    ucfirst($sponsor->status),
                    $sponsor->is_verified ? 'Yes' : 'No',
                    $sponsor->donation_type_label ?? $sponsor->donation_type,
                    $sponsor->formatted_donations ?? '0.00',
                    $sponsor->created_at->format('Y-m-d H:i:s'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'count' => $sponsors->count(),
                'exported_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting sponsors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export sponsors.'
            ], 500);
        }
    }

    /**
     * Get sponsor statistics.
     */
    public function statistics()
    {
        if (!auth()->user()->can('sponsor.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view statistics.'
            ], 403);
        }

        try {
            $stats = [
                'total' => Sponsor::count(),
                'by_type' => Sponsor::select('type', DB::raw('count(*) as total'))
                    ->groupBy('type')
                    ->pluck('total', 'type')
                    ->toArray(),
                'by_status' => Sponsor::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray(),
                'by_donation_type' => Sponsor::select('donation_type', DB::raw('count(*) as total'))
                    ->whereNotNull('donation_type')
                    ->groupBy('donation_type')
                    ->pluck('total', 'donation_type')
                    ->toArray(),
                'verified' => Sponsor::where('is_verified', true)->count(),
                'unverified' => Sponsor::where('is_verified', false)->count(),
                'with_products' => Sponsor::has('products')->count(),
                'without_products' => Sponsor::doesntHave('products')->count(),
                'total_donations' => Sponsor::sum('total_donations'),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching sponsor statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Get sponsors by type.
     */
    public function byType($type)
    {
        if (!auth()->user()->can('sponsor.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view sponsors.'
            ], 403);
        }

        try {
            $sponsors = Sponsor::where('type', $type)->get();

            return response()->json([
                'success' => true,
                'type' => $type,
                'count' => $sponsors->count(),
                'sponsors' => $sponsors
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching sponsors by type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sponsors.'
            ], 500);
        }
    }

    /**
     * Get sponsors by donation type.
     */
    public function byDonationType($donationType)
    {
        if (!auth()->user()->can('sponsor.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view sponsors.'
            ], 403);
        }

        try {
            $sponsors = Sponsor::where('donation_type', $donationType)->get();

            return response()->json([
                'success' => true,
                'donation_type' => $donationType,
                'count' => $sponsors->count(),
                'sponsors' => $sponsors
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching sponsors by donation type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sponsors.'
            ], 500);
        }
    }

    /**
     * Get top sponsors by donations.
     */
    public function topSponsors(Request $request)
    {
        if (!auth()->user()->can('sponsor.view')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view sponsors.'
            ], 403);
        }

        try {
            $limit = $request->limit ?? 10;
            $sponsors = Sponsor::orderBy('total_donations', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'sponsors' => $sponsors
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching top sponsors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch top sponsors.'
            ], 500);
        }
    }
}
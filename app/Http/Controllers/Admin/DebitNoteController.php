<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DebitNote;
use App\Models\DebitNoteItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Currency;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class DebitNoteController extends Controller
{
    /**
     * Display a listing of debit notes.
     * Requires 'debit_note.view' permission.
     */
    public function index()
    {
        if (!auth()->user()->can('debit_note.view')) {
            abort(403, 'You do not have permission to view debit notes.');
        }

        $debitNotes = DebitNote::with(['supplier', 'user', 'currency'])->latest()->paginate(15);

        $stats = [
            'total' => DebitNote::count(),
            'draft' => DebitNote::where('status', 'draft')->count(),
            'approved' => DebitNote::where('status', 'approved')->count(),
            'rejected' => DebitNote::where('status', 'rejected')->count(),
            'cancelled' => DebitNote::where('status', 'cancelled')->count(),
        ];

        $suppliers = Supplier::where('is_active', 1)->get();
        $products = Product::where('is_active', 1)->get();
        $currencies = Currency::where('is_active', 1)->get();

        return view('admin.debit-notes.index', compact('debitNotes', 'stats', 'suppliers', 'products', 'currencies'));
    }

    // No create() method – handled by modal

    public function store(Request $request)
    {
        if (!auth()->user()->can('debit_note.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create debit notes.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'currency_id' => 'required|exists:currencies,id',
                'debit_note_date' => 'required|date',
                'reason' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $debitNote = DebitNote::create([
                'debit_note_number' => $this->generateDebitNoteNumber(),
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'currency_id' => $request->currency_id,
                'exchange_rate' => 1,
                'debit_note_date' => $request->debit_note_date,
                'reason' => $request->reason,
                'status' => 'draft',
                'notes' => $request->notes,
                'subtotal' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$itemData['product_id']}");
                }

                $total = $itemData['quantity'] * $itemData['unit_price'];
                DebitNoteItem::create([
                    'debit_note_id' => $debitNote->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $total,
                    'reason' => $itemData['reason'] ?? null,
                ]);
                $subtotal += $total;
            }

            $debitNote->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            Log::info('Debit note created', [
                'debit_note_id' => $debitNote->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Debit note created successfully!',
                'debit_note' => $debitNote
            ]);

        } catch (\Exception $e) {
            Log::error('Debit note creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create debit note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('debit_note.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view debit notes.'
                ], 403);
            }
            abort(403);
        }

        try {
            $debitNote = DebitNote::with(['supplier', 'user', 'currency', 'items.product'])->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($debitNote);
            }
            return view('admin.debit-notes.show', compact('debitNote'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Debit note not found.'
                ], 404);
            }
            abort(404);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->can('debit_note.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit debit notes.'
                ], 403);
            }
            abort(403);
        }

        try {
            $debitNote = DebitNote::with('items')->findOrFail($id);
            if ($debitNote->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft debit notes can be edited.'
                ], 422);
            }

            $suppliers = Supplier::where('is_active', 1)->get();
            $products = Product::where('is_active', 1)->get();
            $currencies = Currency::where('is_active', 1)->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'debit_note' => $debitNote,
                    'suppliers' => $suppliers,
                    'products' => $products,
                    'currencies' => $currencies,
                ]);
            }

            return view('admin.debit-notes.edit', compact('debitNote', 'suppliers', 'products', 'currencies'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Debit note not found.'
                ], 404);
            }
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('debit_note.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit debit notes.'
            ], 403);
        }

        try {
            $debitNote = DebitNote::findOrFail($id);
            if ($debitNote->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft debit notes can be edited.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'currency_id' => 'required|exists:currencies,id',
                'debit_note_date' => 'required|date',
                'reason' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $debitNote->update([
                'supplier_id' => $request->supplier_id,
                'currency_id' => $request->currency_id,
                'debit_note_date' => $request->debit_note_date,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            // Delete old items
            $debitNote->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$itemData['product_id']}");
                }

                $total = $itemData['quantity'] * $itemData['unit_price'];
                DebitNoteItem::create([
                    'debit_note_id' => $debitNote->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $total,
                    'reason' => $itemData['reason'] ?? null,
                ]);
                $subtotal += $total;
            }

            $debitNote->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Debit note updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Debit note update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update debit note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('debit_note.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete debit notes.'
                ], 403);
            }
            abort(403);
        }

        try {
            $debitNote = DebitNote::findOrFail($id);
            if (!in_array($debitNote->status, ['draft', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft or rejected debit notes can be deleted.'
                ], 422);
            }

            $debitNote->items()->delete();
            $debitNote->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Debit note deleted successfully!'
                ]);
            }

            return redirect()->route('admin.debit-notes.index')->with('success', 'Debit note deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete debit note.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete debit note.');
        }
    }

    public function approve($id)
    {
        if (!auth()->user()->can('debit_note.approve')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to approve debit notes.'
            ], 403);
        }

        try {
            $debitNote = DebitNote::findOrFail($id);
            if ($debitNote->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft debit notes can be approved.'
                ], 422);
            }

            // Apply stock changes (increase stock for supplier returns)
            foreach ($debitNote->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $oldQty = $product->quantity;
                    $product->quantity += $item->quantity;
                    $product->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'return',
                        'quantity_change' => $item->quantity,
                        'previous_quantity' => $oldQty,
                        'new_quantity' => $product->quantity,
                        'reference' => $debitNote->debit_note_number,
                        'notes' => "Debit note approved: {$debitNote->reason}",
                    ]);
                }
            }

            $debitNote->update(['status' => 'approved']);

            Log::info('Debit note approved', [
                'debit_note_id' => $debitNote->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Debit note approved successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Debit note approval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve debit note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->can('debit_note.reject')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to reject debit notes.'
            ], 403);
        }

        try {
            $debitNote = DebitNote::findOrFail($id);
            if ($debitNote->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft debit notes can be rejected.'
                ], 422);
            }

            $debitNote->update([
                'status' => 'rejected',
                'notes' => $request->reason ? ($debitNote->notes ? $debitNote->notes . "\n" : '') . "Rejected: " . $request->reason : $debitNote->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Debit note rejected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject debit note.'
            ], 500);
        }
    }

    public function print($id)
    {
        if (!auth()->user()->can('debit_note.print')) {
            abort(403);
        }

        $debitNote = DebitNote::with(['supplier', 'user', 'currency', 'items.product'])->findOrFail($id);
        return view('admin.debit-notes.print', compact('debitNote'));
    }

   public function pdf($id)
    {
        if (!auth()->user()->can('debit_note.pdf')) {
            abort(403);
        }

        $debitNote = DebitNote::with(['supplier', 'currency', 'items.product'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.debit-notes.pdf', compact('debitNote'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('debit-note-' . $debitNote->debit_note_number . '.pdf');
    }

    public function export()
    {
        if (!auth()->user()->can('debit_note.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    private function generateDebitNoteNumber()
    {
        $prefix = 'DN';
        $date = now()->format('Ymd');
        $last = DebitNote::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
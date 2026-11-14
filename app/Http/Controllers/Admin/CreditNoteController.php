<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Currency;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditNoteController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('credit_note.view')) {
            abort(403, 'You do not have permission to view credit notes.');
        }

        $creditNotes = CreditNote::with(['customer', 'sale', 'user', 'currency'])->latest()->paginate(15);

        $stats = [
            'total' => CreditNote::count(),
            'draft' => CreditNote::where('status', 'draft')->count(),
            'approved' => CreditNote::where('status', 'approved')->count(),
            'rejected' => CreditNote::where('status', 'rejected')->count(),
            'cancelled' => CreditNote::where('status', 'cancelled')->count(),
        ];

        $customers = Customer::where('is_active', 1)->get();
        $sales = Sale::whereIn('payment_status', ['paid', 'partial'])->get();
        $products = Product::where('is_active', 1)->get();
        $currencies = Currency::where('is_active', 1)->get();

        return view('admin.credit-notes.index', compact('creditNotes', 'stats', 'customers', 'sales', 'products', 'currencies'));
    }

    // No create() method – modal

    public function store(Request $request)
    {
        if (!auth()->user()->can('credit_note.create')) {
            return response()->json(['success' => false, 'error' => 'Permission denied.'], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'sale_id' => 'required|exists:sales,id',
                'currency_id' => 'required|exists:currencies,id',
                'credit_note_date' => 'required|date',
                'reason' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $sale = Sale::find($request->sale_id);
            if (!$sale) {
                throw new \Exception("Sale not found with ID: {$request->sale_id}");
            }

            $creditNote = CreditNote::create([
                'credit_note_number' => $this->generateCreditNoteNumber(),
                'customer_id' => $request->customer_id,
                'sale_id' => $request->sale_id,
                'user_id' => auth()->id(),
                'currency_id' => $request->currency_id,
                'exchange_rate' => 1,
                'credit_note_date' => $request->credit_note_date,
                'reason' => $request->reason,
                'status' => 'draft',
                'notes' => $request->notes,
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$itemData['product_id']}");
                }
                if ($product->quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->quantity}");
                }

                $total = $itemData['quantity'] * $itemData['unit_price'];
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $total,
                    'reason' => $itemData['reason'] ?? null,
                ]);
                $subtotal += $total;
            }

            $creditNote->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            return response()->json(['success' => true, 'message' => 'Credit note created successfully!']);
        } catch (\Exception $e) {
            Log::error('Credit note creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to create credit note: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('credit_note.view')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Permission denied.'], 403);
            }
            abort(403);
        }
        try {
            $creditNote = CreditNote::with(['customer', 'sale', 'user', 'currency', 'items.product'])->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($creditNote);
            }
            return view('admin.credit-notes.show', compact('creditNote'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Credit note not found.'], 404);
            }
            abort(404);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->can('credit_note.edit')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Permission denied.'], 403);
            }
            abort(403);
        }
        try {
            $creditNote = CreditNote::with('items')->findOrFail($id);
            if ($creditNote->status !== 'draft') {
                return response()->json(['success' => false, 'error' => 'Only draft credit notes can be edited.'], 422);
            }

            $customers = Customer::where('is_active', 1)->get();
            $sales = Sale::whereIn('payment_status', ['paid', 'partial'])->get();
            $products = Product::where('is_active', 1)->get();
            $currencies = Currency::where('is_active', 1)->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'credit_note' => $creditNote,
                    'customers' => $customers,
                    'sales' => $sales,
                    'products' => $products,
                    'currencies' => $currencies,
                ]);
            }

            return view('admin.credit-notes.edit', compact('creditNote', 'customers', 'sales', 'products', 'currencies'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Credit note not found.'], 404);
            }
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('credit_note.edit')) {
            return response()->json(['success' => false, 'error' => 'Permission denied.'], 403);
        }
        try {
            $creditNote = CreditNote::findOrFail($id);
            if ($creditNote->status !== 'draft') {
                return response()->json(['success' => false, 'error' => 'Only draft credit notes can be edited.'], 422);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'sale_id' => 'required|exists:sales,id',
                'currency_id' => 'required|exists:currencies,id',
                'credit_note_date' => 'required|date',
                'reason' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $creditNote->update([
                'customer_id' => $request->customer_id,
                'sale_id' => $request->sale_id,
                'currency_id' => $request->currency_id,
                'credit_note_date' => $request->credit_note_date,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            $creditNote->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$itemData['product_id']}");
                }
                if ($product->quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $total = $itemData['quantity'] * $itemData['unit_price'];
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $total,
                    'reason' => $itemData['reason'] ?? null,
                ]);
                $subtotal += $total;
            }

            $creditNote->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            return response()->json(['success' => true, 'message' => 'Credit note updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Credit note update failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to update credit note: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('credit_note.delete')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Permission denied.'], 403);
            }
            abort(403);
        }
        try {
            $creditNote = CreditNote::findOrFail($id);
            if (!in_array($creditNote->status, ['draft', 'rejected'])) {
                return response()->json(['success' => false, 'error' => 'Only draft or rejected credit notes can be deleted.'], 422);
            }

            $creditNote->items()->delete();
            $creditNote->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Credit note deleted successfully!']);
            }
            return redirect()->route('admin.credit-notes.index')->with('success', 'Credit note deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Failed to delete credit note.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete credit note.');
        }
    }

    public function approve($id)
    {
        if (!auth()->user()->can('credit_note.approve')) {
            return response()->json(['success' => false, 'error' => 'Permission denied.'], 403);
        }
        try {
            $creditNote = CreditNote::findOrFail($id);
            if ($creditNote->status !== 'draft') {
                return response()->json(['success' => false, 'error' => 'Only draft credit notes can be approved.'], 422);
            }

            // Apply stock changes (decrease stock for customer returns)
            foreach ($creditNote->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    if ($product->quantity < $item->quantity) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                    $oldQty = $product->quantity;
                    $product->quantity -= $item->quantity;
                    $product->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'return',
                        'quantity_change' => -$item->quantity,
                        'previous_quantity' => $oldQty,
                        'new_quantity' => $product->quantity,
                        'reference' => $creditNote->credit_note_number,
                        'notes' => "Credit note approved: {$creditNote->reason}",
                    ]);
                }
            }

            $creditNote->update(['status' => 'approved']);

            // Update sale
            $sale = $creditNote->sale;
            if ($sale) {
                $sale->credit_note_id = $creditNote->id;
                $sale->save();
            }

            return response()->json(['success' => true, 'message' => 'Credit note approved successfully!']);
        } catch (\Exception $e) {
            Log::error('Credit note approval failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to approve credit note: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->can('credit_note.reject')) {
            return response()->json(['success' => false, 'error' => 'Permission denied.'], 403);
        }
        try {
            $creditNote = CreditNote::findOrFail($id);
            if ($creditNote->status !== 'draft') {
                return response()->json(['success' => false, 'error' => 'Only draft credit notes can be rejected.'], 422);
            }

            $creditNote->update([
                'status' => 'rejected',
                'notes' => $request->reason ? ($creditNote->notes ? $creditNote->notes . "\n" : '') . "Rejected: " . $request->reason : $creditNote->notes,
            ]);

            return response()->json(['success' => true, 'message' => 'Credit note rejected successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to reject credit note.'], 500);
        }
    }

    public function print($id)
    {
        if (!auth()->user()->can('credit_note.print')) {
            abort(403);
        }
        $creditNote = CreditNote::with(['customer', 'sale', 'user', 'currency', 'items.product'])->findOrFail($id);
        return view('admin.credit-notes.print', compact('creditNote'));
    }

    public function pdf($id)
    {
        if (!auth()->user()->can('credit_note.pdf')) {
            abort(403);
        }
        $creditNote = CreditNote::with(['customer', 'sale', 'currency', 'items.product'])->findOrFail($id);
        $pdf = Pdf::loadView('admin.credit-notes.pdf', compact('creditNote'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('credit-note-' . $creditNote->credit_note_number . '.pdf');
    }

    public function export()
    {
        if (!auth()->user()->can('credit_note.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    private function generateCreditNoteNumber()
    {
        $prefix = 'CN';
        $date = now()->format('Ymd');
        $last = CreditNote::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
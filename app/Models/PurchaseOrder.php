<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'supplier_id',
        'user_id',
        'currency_id',
        'exchange_rate',
        'po_number',
        'supplier_invoice_number',
        'order_date',
        'invoice_date',
        'due_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'total_amount',
        'paid_amount',
        'payment_status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'invoice_date' => 'date',
            'due_date' => 'date',
            'expected_delivery_date' => 'date',
            'actual_delivery_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the supplier for this purchase order.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created this purchase order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the currency used for this purchase order.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the items (line items) for this purchase order.
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the procurement record linked to this purchase order (if any).
     */
    public function procurement()
    {
        return $this->hasOne(Procurement::class);
    }

    /**
     * Get all purchase payments made against this purchase order.
     * Uses the dedicated purchase_payments table.
     */
    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_order_id');
    }

    /**
     * Alias for purchasePayments() – kept for consistency if you prefer ->payments().
     */
    public function payments()
    {
        return $this->purchasePayments();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the purchase order has been received.
     */
    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    /**
     * Check if the purchase order has been ordered (submitted).
     */
    public function isOrdered(): bool
    {
        return $this->status === 'ordered';
    }

    /**
     * Calculate the total amount from all order items.
     */
    public function calculateTotal(): float
    {
        return $this->items()->sum('total_price');
    }

    /**
     * Recalculate paid amount and payment status from all linked purchase payments.
     */
    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->purchasePayments()->sum('amount');

        $this->paid_amount = $totalPaid;
        $this->payment_status = $totalPaid >= $this->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');

        // If fully paid, also promote main status to 'paid'
        if ($this->payment_status === 'paid' && $this->status !== 'paid') {
            $this->status = 'paid';
        }

        $this->save();
    }
    
}
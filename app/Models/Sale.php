<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'customer_id', 'user_id',
        'warehouse_id', 'sale_date', 'due_date',
        'currency_id', 'exchange_rate',
        'subtotal', 'discount_type', 'discount',
        'tax', 'shipping_cost', 'total',
        'payment_method', 'payment_status',
        'amount_paid', 'balance',
        'notes', 'internal_notes',
        'quotation_id'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'due_date' => 'date',
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PARTIAL = 'partial';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_CANCELLED = 'cancelled';

    const METHOD_CASH = 'cash';
    const METHOD_CARD = 'card';
    const METHOD_MOBILE = 'mobile';
    const METHOD_CREDIT = 'credit';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHEQUE = 'cheque';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function isPaid()
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isPending()
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }

    public function isPartial()
    {
        return $this->payment_status === self::PAYMENT_PARTIAL;
    }

    public function isCancelled()
    {
        return $this->payment_status === self::PAYMENT_CANCELLED;
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date < now() 
            && $this->payment_status !== self::PAYMENT_PAID;
    }

    public function getBalanceAttribute()
    {
        return $this->total - $this->amount_paid;
    }

    public function getStatusBadgeClass()
    {
        return match($this->payment_status) {
            self::PAYMENT_PAID => 'bg-success',
            self::PAYMENT_PENDING => 'bg-warning',
            self::PAYMENT_PARTIAL => 'bg-info',
            self::PAYMENT_CANCELLED => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', self::PAYMENT_PENDING);
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', self::PAYMENT_PARTIAL);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('sale_date', [$from, $to]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('payment_status', [self::PAYMENT_PENDING, self::PAYMENT_PARTIAL]);
    }
    public function payments()
{
    return $this->hasMany(SalesPayment::class);
}
}
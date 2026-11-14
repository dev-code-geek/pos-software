<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'sale_id', 'customer_id',
        'invoice_date', 'due_date',
        'subtotal', 'discount', 'tax', 'total',
        'amount_paid', 'balance', 'payment_status',
        'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isPaid()
    {
        return $this->payment_status === self::STATUS_PAID;
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date < now() 
            && $this->payment_status !== self::STATUS_PAID;
    }

    public function getBalanceAttribute()
    {
        return $this->total - $this->amount_paid;
    }

    public function getStatusBadgeClass()
    {
        return match($this->payment_status) {
            self::STATUS_PAID => 'bg-success',
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_PARTIAL => 'bg-info',
            self::STATUS_OVERDUE => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::STATUS_PAID);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('payment_status', [self::STATUS_PENDING, self::STATUS_PARTIAL]);
    }

    /**
     * Get all sales payments for this invoice.
     */
    public function salesPayments()
    {
        return $this->hasMany(SalesPayment::class);
    }

    /**
     * Alias for payments (consistent naming).
     */
    public function payments()
    {
        return $this->salesPayments();
    }

    /**
     * Recalculate paid amount and payment status from payments.
     */
    public function updatePaymentStatus()
    {
        $totalPaid = $this->salesPayments()->sum('amount');
        $this->amount_paid = $totalPaid;
        $this->balance = $this->total - $totalPaid;
        $this->payment_status = $totalPaid >= $this->total ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');
        $this->save();

        // Optionally update the linked sale if needed
        if ($this->sale) {
            $this->sale->amount_paid = $totalPaid;
            $this->sale->payment_status = $this->payment_status;
            $this->sale->save();
        }
    }
    
}
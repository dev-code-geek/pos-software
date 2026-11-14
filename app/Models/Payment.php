<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'paymentable_type',
        'paymentable_id',
        'amount',
        'currency_id',
        'exchange_rate',
        'payment_date',
        'method',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'payment_date' => 'date',
    ];

    // Polymorphic parent
    public function paymentable()
    {
        return $this->morphTo();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate parent entity's paid amount and payment status.
     */
    public static function recalcParent(Payment $payment)
    {
        $parent = $payment->paymentable;
        if (!$parent) return;

        $totalPaid = $parent->payments()->sum('amount');
        $parent->paid_amount = $totalPaid;
        $parent->payment_status = $totalPaid >= $parent->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');
        $parent->save();

        // Optional: update main status if fully paid
        if (method_exists($parent, 'updatePaymentStatus')) {
            $parent->updatePaymentStatus();
        }
    }
}
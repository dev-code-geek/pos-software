<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number', 'customer_id', 'user_id',
        'warehouse_id', 'quotation_date', 'valid_until',
        'currency_id', 'exchange_rate',
        'subtotal', 'discount_type', 'discount',
        'tax', 'total', 'notes', 'internal_notes',
        'status', 'converted_to_sale_id'
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CONVERTED = 'converted';

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
        return $this->hasMany(QuotationItem::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'converted_to_sale_id');
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent()
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isConverted()
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    public function canConvert()
    {
        return $this->status === self::STATUS_ACCEPTED && !$this->converted_to_sale_id;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_SENT => 'bg-info',
            self::STATUS_ACCEPTED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_EXPIRED => 'bg-warning',
            self::STATUS_CONVERTED => 'bg-primary',
            default => 'bg-secondary'
        };
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeConverted($query)
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('quotation_date', [$from, $to]);
    }
}
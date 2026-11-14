<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Procurement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_request_id',
        'purchase_order_id',
        'supplier_id',
        'procurement_officer_id',
        'order_date',
        'expected_delivery',
        'actual_delivery',
        'delivery_notes',
        'delivery_status',
        'received_by',
        'received_date',
        'total_cost',
        'payment_status',
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
            'expected_delivery' => 'date',
            'actual_delivery' => 'date',
            'received_date' => 'date',
            'total_cost' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the asset request for this procurement.
     */
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    /**
     * Get the purchase order for this procurement.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the supplier for this procurement.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the procurement officer.
     */
    public function procurementOfficer()
    {
        return $this->belongsTo(User::class, 'procurement_officer_id');
    }

    /**
     * Get the user who received the items.
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Check if procurement is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->delivery_status === 'delivered';
    }

    /**
     * Check if procurement is pending.
     */
    public function isPending(): bool
    {
        return $this->delivery_status === 'pending';
    }
}
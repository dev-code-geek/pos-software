<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'warehouse_id',
        'name',
        'sku',
        'barcode',
        'description',
        'type',                     // free text: e.g., 'hardware', 'software', 'accessory'
        'purchase_date',
        'warranty_end_date',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'cost_price',
        'current_value',
        'status',
        'quantity',
        'min_stock_level',
        'reorder_level',
        'reorder_quantity',
        'unit',
        'weight',
        'dimensions',
        'is_active',
        'is_taxable',
        'tax_rate',
    ];

    protected $casts = [
        'purchase_date'          => 'date',
        'warranty_end_date'      => 'date',
        'purchase_price'         => 'decimal:2',
        'selling_price'          => 'decimal:2',
        'wholesale_price'        => 'decimal:2',
        'cost_price'             => 'decimal:2',
        'current_value'          => 'decimal:2',
        'weight'                 => 'decimal:3',
        'quantity'               => 'integer',
        'min_stock_level'        => 'integer',
        'reorder_level'          => 'integer',
        'reorder_quantity'       => 'integer',
        'tax_rate'               => 'decimal:2',
        'is_active'              => 'boolean',
        'is_taxable'             => 'boolean',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function debitNoteItems()
    {
        return $this->hasMany(DebitNoteItem::class);
    }

    public function creditNoteItems()
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function stockAdjustmentItems()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Helpers
    public function isAvailable()
    {
        return $this->status === 'available' && $this->quantity > 0;
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->min_stock_level && $this->quantity > 0;
    }

    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    public function getFormattedSellingPriceAttribute()
    {
        return number_format($this->selling_price, 2);
    }

    public function getProfitAttribute()
    {
        return ($this->selling_price ?? 0) - ($this->cost_price ?? 0);
    }

    public function getProfitMarginAttribute()
    {
        if (empty($this->selling_price) || $this->selling_price <= 0) {
            return 0;
        }
        return round(($this->profit / $this->selling_price) * 100, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock_level')
            ->where('quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

     public function recalcTotalQuantity()
    {
        $this->quantity = $this->stocks()->sum('quantity');
        $this->save();
    }
}
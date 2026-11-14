<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'sku', 'name', 'selling_price',
        'cost_price', 'quantity', 'attributes',
        'barcode', 'image', 'is_active'
    ];

    protected $casts = [
        'attributes' => 'array',
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class, 'variant_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'variant_id');
    }

    public function stockAdjustmentItems()
    {
        return $this->hasMany(StockAdjustmentItem::class, 'variant_id');
    }

    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItem::class, 'variant_id');
    }

    public function isAvailable()
    {
        return $this->quantity > 0 && $this->is_active;
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->selling_price, 2);
    }
}
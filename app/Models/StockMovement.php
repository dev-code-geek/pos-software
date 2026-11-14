<?php
// app/Models/StockMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity_change',
        'previous_quantity',
        'new_quantity',
        'reference',
        'notes'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'quantity_change' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
    ];

    /**
     * Get the product for this stock movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who performed the stock movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a purchase movement (stock-in).
     */
    public function isPurchase(): bool
    {
        return $this->type === 'purchase';
    }

    /**
     * Check if this is an adjustment movement.
     */
    public function isAdjustment(): bool
    {
        return $this->type === 'adjustment';
    }

    /**
     * Check if this is a return movement (stock-in).
     */
    public function isReturn(): bool
    {
        return $this->type === 'return';
    }

    /**
     * Check if this is a damage movement (stock-out).
     */
    public function isDamage(): bool
    {
        return $this->type === 'damage';
    }

    /**
     * Check if this is an incoming movement (positive quantity change).
     */
    public function isIncoming(): bool
    {
        return $this->quantity_change > 0;
    }

    /**
     * Check if this is an outgoing movement (negative quantity change).
     */
    public function isOutgoing(): bool
    {
        return $this->quantity_change < 0;
    }

    /**
     * Get the type label in human-readable format.
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'purchase' => 'Purchase',
            'adjustment' => 'Stock Adjustment',
            'return' => 'Return',
            'damage' => 'Damaged Stock',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the type badge class for UI.
     */
    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            'purchase' => 'bg-green-100 text-green-800',
            'adjustment' => 'bg-yellow-100 text-yellow-800',
            'return' => 'bg-blue-100 text-blue-800',
            'damage' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the type icon for UI.
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            'purchase' => 'fa-shopping-cart',
            'adjustment' => 'fa-sliders-h',
            'return' => 'fa-undo',
            'damage' => 'fa-exclamation-triangle',
            default => 'fa-circle',
        };
    }

    /**
     * Get the direction icon based on quantity change.
     */
    public function getDirectionIcon(): string
    {
        if ($this->isIncoming()) {
            return 'fa-arrow-down text-green-600';
        }
        return 'fa-arrow-up text-red-600';
    }

    /**
     * Get the quantity change with sign.
     */
    public function getSignedQuantity(): string
    {
        $sign = $this->isIncoming() ? '+' : '';
        return $sign . $this->quantity_change;
    }

    /**
     * Get the formatted quantity change.
     */
    public function getFormattedQuantity(): string
    {
        $sign = $this->isIncoming() ? '+' : '';
        return $sign . number_format($this->quantity_change);
    }

    /**
     * Scope for incoming movements.
     */
    public function scopeIncoming($query)
    {
        return $query->where('quantity_change', '>', 0);
    }

    /**
     * Scope for outgoing movements.
     */
    public function scopeOutgoing($query)
    {
        return $query->where('quantity_change', '<', 0);
    }

    /**
     * Scope for purchase movements.
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    /**
     * Scope for adjustment movements.
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * Scope for return movements.
     */
    public function scopeReturns($query)
    {
        return $query->where('type', 'return');
    }

    /**
     * Scope for damage movements.
     */
    public function scopeDamages($query)
    {
        return $query->where('type', 'damage');
    }

    /**
     * Scope for movements of a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for movements by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for movements within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for today's movements.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this month's movements.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Get the total incoming quantity for a product.
     */
    public static function getTotalIncoming($productId): int
    {
        return static::forProduct($productId)
            ->incoming()
            ->sum('quantity_change');
    }

    /**
     * Get the total outgoing quantity for a product.
     */
    public static function getTotalOutgoing($productId): int
    {
        return static::forProduct($productId)
            ->outgoing()
            ->sum('quantity_change');
    }

    /**
     * Get the net movement for a product.
     */
    public static function getNetMovement($productId): int
    {
        return static::getTotalIncoming($productId) + static::getTotalOutgoing($productId);
    }

    /**
     * Get the current stock level for a product.
     */
    public static function getCurrentStock($productId): int
    {
        $lastMovement = static::forProduct($productId)
            ->latest('created_at')
            ->first();

        return $lastMovement ? $lastMovement->new_quantity : 0;
    }

    /**
     * Get movement summary by type.
     */
    public static function getSummaryByType($productId = null): array
    {
        $query = static::query();
        
        if ($productId) {
            $query->forProduct($productId);
        }

        return [
            'purchase' => $query->clone()->purchases()->sum('quantity_change'),
            'return' => $query->clone()->returns()->sum('quantity_change'),
            'adjustment' => $query->clone()->adjustments()->sum('quantity_change'),
            'damage' => $query->clone()->damages()->sum('quantity_change'),
        ];
    }

    /**
     * Get daily movement summary.
     */
    public static function getDailySummary($date = null): array
    {
        $date = $date ?? today();
        
        return [
            'total_movements' => static::whereDate('created_at', $date)->count(),
            'incoming' => static::whereDate('created_at', $date)->incoming()->sum('quantity_change'),
            'outgoing' => static::whereDate('created_at', $date)->outgoing()->sum('quantity_change'),
            'net' => static::whereDate('created_at', $date)->incoming()->sum('quantity_change') +
                     static::whereDate('created_at', $date)->outgoing()->sum('quantity_change'),
        ];
    }

    /**
     * Get movement history with product details.
     */
    public function getHistoryWithProduct()
    {
        return $this->with(['product', 'user'])->latest('created_at')->get();
    }

    /**
     * Format the movement details for display.
     */
    public function getFormattedDetails(): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product->name ?? 'N/A',
            'type' => $this->getTypeLabel(),
            'quantity' => $this->getFormattedQuantity(),
            'previous_quantity' => number_format($this->previous_quantity),
            'new_quantity' => number_format($this->new_quantity),
            'reference' => $this->reference ?? 'N/A',
            'notes' => $this->notes ?? 'N/A',
            'user' => $this->user->name ?? 'N/A',
            'date' => $this->created_at->format('Y-m-d H:i:s'),
            'time_ago' => $this->created_at->diffForHumans(),
        ];
    }

    /**
     * Create a movement entry and update product stock.
     */
    public static function recordMovement(
        $productId,
        $userId,
        $type,
        $quantity,
        $reference = null,
        $notes = null
    ): self {
        $product = Product::findOrFail($productId);
        
        // Calculate new quantities
        $previousQuantity = $product->quantity;
        
        if ($quantity > 0) {
            // Incoming movement (increase stock)
            $newQuantity = $previousQuantity + $quantity;
        } else {
            // Outgoing movement (decrease stock)
            $newQuantity = $previousQuantity + $quantity; // quantity is negative
        }
        
        // Create movement record
        $movement = self::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'type' => $type,
            'quantity_change' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reference' => $reference,
            'notes' => $notes,
        ]);
        
        // Update product quantity
        $product->update(['quantity' => $newQuantity]);
        
        // If quantity is 0, update status
        if ($newQuantity <= 0 && $product->status === 'available') {
            $product->update(['status' => 'assigned']);
        } elseif ($newQuantity > 0 && $product->status === 'assigned') {
            $product->update(['status' => 'available']);
        }
        
        return $movement;
    }

    /**
     * Get the absolute quantity change.
     */
    public function getAbsoluteQuantity(): int
    {
        return abs($this->quantity_change);
    }

    /**
     * Check if this movement is related to a purchase order.
     */
    public function hasPurchaseOrder(): bool
    {
        return $this->isPurchase() && $this->reference && str_starts_with($this->reference, 'PO-');
    }

    /**
     * Get the purchase order reference if available.
     */
    public function getPurchaseOrderReference(): ?string
    {
        if ($this->hasPurchaseOrder()) {
            return $this->reference;
        }
        return null;
    }
}
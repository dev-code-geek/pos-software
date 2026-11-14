<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'suppliers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'website',
        'tax_id',
        'registration_number',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the products supplied by this supplier.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the purchase orders for this supplier.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the procurements for this supplier.
     */
    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }

    /**
     * Get the inspections where returned to this supplier.
     */
    public function returnedInspections()
    {
        return $this->hasMany(Inspection::class, 'returned_to_supplier_id');
    }

    /**
     * Get the user who created this supplier.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this supplier.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the type label for the supplier.
     */
    public function getTypeLabelAttribute()
    {
        // If you have a type column, use it
        // Otherwise return a default
        return $this->type ?? 'Supplier';
    }

    /**
     * Get the status label for the supplier.
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the status badge class for the supplier.
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'bg-success' : 'bg-secondary';
    }

    /**
     * Get the formatted address.
     */
    public function getFullAddressAttribute()
    {
        return $this->address ?? 'N/A';
    }

    /**
     * Scope a query to only include active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive suppliers.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to search suppliers.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('contact_person', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%")
              ->orWhere('address', 'LIKE', "%{$search}%")
              ->orWhere('tax_id', 'LIKE', "%{$search}%")
              ->orWhere('registration_number', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter suppliers with products.
     */
    public function scopeHasProducts($query)
    {
        return $query->has('products');
    }

    /**
     * Scope a query to filter suppliers without products.
     */
    public function scopeWithoutProducts($query)
    {
        return $query->doesntHave('products');
    }

    /**
     * Scope a query to filter suppliers with purchase orders.
     */
    public function scopeHasPurchaseOrders($query)
    {
        return $query->has('purchaseOrders');
    }

    /**
     * Scope a query to filter suppliers without purchase orders.
     */
    public function scopeWithoutPurchaseOrders($query)
    {
        return $query->doesntHave('purchaseOrders');
    }

    /**
     * Get the total value of products from this supplier.
     */
    public function getTotalProductValueAttribute()
    {
        return $this->products()->sum('purchase_price');
    }

    /**
     * Get the total quantity of products from this supplier.
     */
    public function getTotalProductQuantityAttribute()
    {
        return $this->products()->sum('quantity');
    }

    /**
     * Get the total amount of purchase orders from this supplier.
     */
    public function getTotalPurchaseOrderAmountAttribute()
    {
        return $this->purchaseOrders()->sum('total_amount');
    }

    /**
     * Check if supplier has any products.
     */
    public function hasProducts()
    {
        return $this->products()->count() > 0;
    }

    /**
     * Check if supplier has any purchase orders.
     */
    public function hasPurchaseOrders()
    {
        return $this->purchaseOrders()->count() > 0;
    }

    /**
     * Check if supplier has any procurements.
     */
    public function hasProcurements()
    {
        return $this->procurements()->count() > 0;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
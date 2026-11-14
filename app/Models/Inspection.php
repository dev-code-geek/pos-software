<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inspection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_request_id',
        'product_id',
        'inspected_by',
        'inspection_date',
        'result',
        'physical_condition',
        'functional_condition',
        'issues_found',
        'recommendations',
        'is_returned',
        'return_reason',
        'return_date',
        'returned_to_supplier_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'return_date' => 'date',
            'is_returned' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the asset request for this inspection.
     */
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    /**
     * Get the product being inspected.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the inspector.
     */
    public function inspectedBy()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    /**
     * Get the supplier the product was returned to.
     */
    public function returnedToSupplier()
    {
        return $this->belongsTo(Supplier::class, 'returned_to_supplier_id');
    }

    /**
     * Check if inspection passed.
     */
    public function isPassed(): bool
    {
        return $this->result === 'passed';
    }

    /**
     * Check if inspection failed.
     */
    public function isFailed(): bool
    {
        return $this->result === 'failed';
    }
}
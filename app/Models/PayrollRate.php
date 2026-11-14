<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_type',
        'rate_value',
        'income_from',
        'income_to',
        'effective_from',
        'effective_to',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate_value' => 'decimal:2',
        'income_from' => 'decimal:2',
        'income_to' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    const TYPE_PAYE = 'PAYE';
    const TYPE_NSSF = 'NSSF';
    const TYPE_PPF = 'PPF';
    const TYPE_TRANSPORT_ALLOWANCE = 'TRANSPORT_ALLOWANCE';
    const TYPE_HOUSING_ALLOWANCE = 'HOUSING_ALLOWANCE';
    const TYPE_FOOD_ALLOWANCE = 'FOOD_ALLOWANCE';
    const TYPE_MEDICAL_ALLOWANCE = 'MEDICAL_ALLOWANCE';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isActive()
    {
        return $this->is_active && 
               ($this->effective_to === null || $this->effective_to >= now()->toDateString());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->where('effective_to', '>=', now()->toDateString())
                  ->orWhereNull('effective_to');
            });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('rate_type', $type);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('effective_from', '<=', $date)
            ->where(function($q) use ($date) {
                $q->where('effective_to', '>=', $date)
                  ->orWhereNull('effective_to');
            });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'short_name', 'registration_number', 'tax_id',
        'email', 'phone', 'address', 'city', 'state', 'country',
        'postal_code', 'logo', 'currency_id',
        'default_discount', 'default_tax',
        'invoice_prefix', 'quotation_prefix',
        'debit_note_prefix', 'credit_note_prefix',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_discount' => 'decimal:2',
        'default_tax' => 'decimal:2'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getFullAddressAttribute()
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->country,
            $this->postal_code
        ]));
    }
}
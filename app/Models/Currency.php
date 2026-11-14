<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'symbol', 'decimal_places',
        'is_base', 'exchange_rate', 'is_active'
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'exchange_rate' => 'decimal:6',
        'decimal_places' => 'integer'
    ];

    public function exchangeRatesFrom()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    public function exchangeRatesTo()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function debitNotes()
    {
        return $this->hasMany(DebitNote::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function getFormattedRateAttribute()
    {
        return number_format($this->exchange_rate, 4);
    }

    public function getSymbolCodeAttribute()
    {
        return $this->symbol . ' ' . $this->code;
    }

    public function isBaseCurrency()
    {
        return $this->is_base;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBase($query)
    {
        return $query->where('is_base', true);
    }
}
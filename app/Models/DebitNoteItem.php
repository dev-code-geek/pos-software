<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'debit_note_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function debitNote()
    {
        return $this->belongsTo(DebitNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
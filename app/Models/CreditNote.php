<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'credit_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'credit_note_number',
        'customer_id',
        'sale_id',
        'user_id',
        'currency_id',
        'exchange_rate',
        'credit_note_date',
        'reason',
        'status',
        'subtotal',
        'tax',
        'total',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_note_date' => 'date',
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the customer associated with the credit note.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sale associated with the credit note.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user who created the credit note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the currency associated with the credit note.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the items for the credit note.
     */
    public function items()
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    /**
     * Check if credit note is draft.
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if credit note is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if credit note is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
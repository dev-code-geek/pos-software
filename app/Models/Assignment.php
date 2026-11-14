<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'assigned_by',
        'assigned_date',
        'expected_return_date',
        'actual_return_date',
        'condition_out',
        'condition_in',
        'assigned_license_key',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'expected_return_date' => 'date',
            'actual_return_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the product being assigned.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user the product is assigned to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who assigned the product.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Check if assignment is active.
     */
    public function isActive(): bool
    {
        return is_null($this->actual_return_date);
    }

    /**
     * Check if assignment is returned.
     */
    public function isReturned(): bool
    {
        return !is_null($this->actual_return_date);
    }

    /**
     * Check if assignment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->isActive() && $this->expected_return_date && $this->expected_return_date < now();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'issue_description',
        'priority',
        'status',
        'cost',
        'repaired_by',
        'repair_date',
        'resolved_date',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'repair_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
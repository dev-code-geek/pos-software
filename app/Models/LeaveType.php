<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'leave_code',
        'leave_name',
        'description',
        'days_per_year',
        'is_paid',
        'requires_approval',
        'requires_medical_certificate',
        'max_consecutive_days',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'days_per_year' => 'decimal:1',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'requires_medical_certificate' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function requests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasRequests()
    {
        return $this->requests()->exists();
    }
}
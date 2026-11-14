<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'category',
        'code',
        'registration_number',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'contact_person',
        'contact_phone',
        'contact_email',
        'description',
        'status',
        'is_verified',
        'verified_at',
        'verified_by',
        'donation_type',
        'total_donations',
        'donation_currency',
        'last_donation_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'total_donations' => 'decimal:2',
        'last_donation_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created this sponsor.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this sponsor.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who verified this sponsor.
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the products for this sponsor.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the full address attribute.
     */
    public function getFullAddressAttribute()
    {
        $parts = [];
        if ($this->address) $parts[] = $this->address;
        if ($this->city) $parts[] = $this->city;
        if ($this->state) $parts[] = $this->state;
        if ($this->country) $parts[] = $this->country;
        if ($this->postal_code) $parts[] = $this->postal_code;
        return implode(', ', $parts);
    }

    /**
     * Get the sponsor type label.
     */
    public function getTypeLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Get the donation type label.
     */
    public function getDonationTypeLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->donation_type ?? 'N/A'));
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeAttribute()
    {
        $classes = [
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'pending' => 'bg-warning text-dark',
            'suspended' => 'bg-danger',
        ];
        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Get formatted total donations.
     */
    public function getFormattedDonationsAttribute()
    {
        if (!$this->total_donations) return '0.00';
        return $this->donation_currency . ' ' . number_format($this->total_donations, 2);
    }

    /**
     * Scope a query to only include active sponsors.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include verified sponsors.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include sponsors of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Search sponsors by name, code, or contact.
     */
    public static function search($term)
    {
        return self::where('name', 'LIKE', "%{$term}%")
            ->orWhere('code', 'LIKE', "%{$term}%")
            ->orWhere('contact_person', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")
            ->orWhere('phone', 'LIKE', "%{$term}%")
            ->orWhere('registration_number', 'LIKE', "%{$term}%");
    }
}
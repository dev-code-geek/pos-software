<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareLicenseUsage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'software_license_usage'; // <-- Important!

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'facility_id',
        'assigned_to_type',
        'license_key',
        'assigned_at',
        'revoked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'date',
            'revoked_at' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the software product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user assigned to this license (if assigned to user).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the facility assigned to this license (if assigned to facility).
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the assignee (either user or facility).
     */
    public function getAssigneeAttribute()
    {
        if ($this->assigned_to_type === 'user' && $this->user) {
            return $this->user;
        }
        if ($this->assigned_to_type === 'facility' && $this->facility) {
            return $this->facility;
        }
        return null;
    }

    /**
     * Get the assignee name.
     */
    public function getAssigneeNameAttribute()
    {
        if ($this->assigned_to_type === 'user' && $this->user) {
            return $this->user->name . ' (User)';
        }
        if ($this->assigned_to_type === 'facility' && $this->facility) {
            return $this->facility->name . ' (Facility)';
        }
        return 'N/A';
    }

    /**
     * Get the assignee type label.
     */
    public function getAssigneeTypeLabelAttribute()
    {
        return $this->assigned_to_type === 'user' ? 'User' : 'Facility';
    }

    /**
     * Get the assignee ID.
     */
    public function getAssigneeIdAttribute()
    {
        if ($this->assigned_to_type === 'user') {
            return $this->user_id;
        }
        return $this->facility_id;
    }

    /**
     * Check if license is active (not revoked).
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }

    /**
     * Check if assigned to a user.
     */
    public function isAssignedToUser(): bool
    {
        return $this->assigned_to_type === 'user';
    }

    /**
     * Check if assigned to a facility.
     */
    public function isAssignedToFacility(): bool
    {
        return $this->assigned_to_type === 'facility';
    }

    /**
     * Scope for active licenses.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope for revoked licenses.
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope for licenses assigned to users.
     */
    public function scopeAssignedToUser($query)
    {
        return $query->where('assigned_to_type', 'user');
    }

    /**
     * Scope for licenses assigned to facilities.
     */
    public function scopeAssignedToFacility($query)
    {
        return $query->where('assigned_to_type', 'facility');
    }

    /**
     * Scope for licenses assigned to a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for licenses assigned to a specific facility.
     */
    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }
}
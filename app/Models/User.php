<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'bio',
        'employee_id',
        'department_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the assignments for the user.
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'user_id');
    }

    /**
     * Get the assignments created by the user.
     */
    public function assignedBy()
    {
        return $this->hasMany(Assignment::class, 'assigned_by');
    }

    /**
     * Get the asset requests created by the user.
     */
    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class, 'user_id');
    }

    /**
     * Get the direct requests created by the user.
     */
    public function directRequests()
    {
        return $this->hasMany(DirectRequest::class, 'user_id');
    }

    /**
     * Get the direct requests where user is the director.
     */
    public function directorRequests()
    {
        return $this->hasMany(DirectRequest::class, 'director_id');
    }

    /**
     * Get the inspections performed by the user.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'inspected_by');
    }

    /**
     * Get the procurements managed by the user.
     */
    public function procurements()
    {
        return $this->hasMany(Procurement::class, 'procurement_officer_id');
    }

    /**
     * Get the purchase orders created by the user.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'user_id');
    }

    /**
     * Get the maintenance logs reported by the user.
     */
    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class, 'user_id');
    }

    /**
     * Get the stock movements created by the user.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'user_id');
    }

    /**
     * Get the software licenses assigned to the user.
     */
    public function softwareLicenses()
    {
        return $this->hasMany(SoftwareLicenseUsage::class, 'user_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Get the workflow actions performed by the user.
     */
    public function workflowActions()
    {
        return $this->hasMany(RequestWorkflow::class, 'actor_id');
    }

    /**
     * Get the specification amendments requested by the user.
     */
    public function requestedAmendments()
    {
        return $this->hasMany(SpecificationAmendment::class, 'requested_by');
    }

    /**
     * Get the specification amendments reviewed by the user.
     */
    public function reviewedAmendments()
    {
        return $this->hasMany(SpecificationAmendment::class, 'reviewed_by');
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is ICT officer.
     */
    public function isIctOfficer(): bool
    {
        return $this->hasRole('ict_officer');
    }

    /**
     * Check if user is director.
     */
    public function isDirector(): bool
    {
        return $this->hasRole('director');
    }

    /**
     * Check if user is procurement officer.
     */
    public function isProcurementOfficer(): bool
    {
        return $this->hasRole('procurement_officer');
    }
}
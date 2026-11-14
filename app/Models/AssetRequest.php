<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'request_type',
        'title',
        'description',
        'specifications',
        'quantity_requested',
        'priority',
        'justification',
        'status',
        'assigned_product_id',
        'ict_notes',
        'director_notes',
        'procurement_notes',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Status constants for easy reference.
     */
    const STATUS_PENDING_ICT_REVIEW = 'pending_ict_review';
    const STATUS_ICT_REVIEWING = 'ict_reviewing';
    const STATUS_APPROVED_BY_ICT = 'approved_by_ict';
    const STATUS_REJECTED_BY_ICT = 'rejected_by_ict';
    const STATUS_SENT_TO_DIRECTOR = 'sent_to_director';
    const STATUS_APPROVED_BY_DIRECTOR = 'approved_by_director';
    const STATUS_REJECTED_BY_DIRECTOR = 'rejected_by_director';
    const STATUS_SENT_TO_PROCUREMENT = 'sent_to_procurement';
    const STATUS_PROCUREMENT_REVIEWING = 'procurement_reviewing';
    const STATUS_SPECS_REVISION_NEEDED = 'specs_revision_needed';
    const STATUS_READY_FOR_PURCHASE = 'ready_for_purchase';
    const STATUS_ORDERED = 'ordered';
    const STATUS_RECEIVED = 'received';
    const STATUS_UNDER_INSPECTION = 'under_inspection';
    const STATUS_INSPECTION_PASSED = 'inspection_passed';
    const STATUS_INSPECTION_FAILED = 'inspection_failed';
    const STATUS_ASSIGNED_TO_USER = 'assigned_to_user';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the user who made the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the requested asset.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the product assigned to fulfill the request.
     */
    public function assignedProduct()
    {
        return $this->belongsTo(Product::class, 'assigned_product_id');
    }

    /**
     * Get the workflow history for this request.
     */
    public function workflows()
    {
        return $this->hasMany(RequestWorkflow::class);
    }

    /**
     * Get the procurement record for this request.
     */
    public function procurement()
    {
        return $this->hasOne(Procurement::class);
    }

    /**
     * Get the inspections for this request.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Get the specification amendments for this request.
     */
    public function specificationAmendments()
    {
        return $this->hasMany(SpecificationAmendment::class);
    }

    /**
     * Get the direct request linked to this asset request.
     */
    public function directRequest()
    {
        return $this->hasOne(DirectRequest::class);
    }

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_ICT_REVIEW,
            self::STATUS_ICT_REVIEWING,
        ]);
    }

    /**
     * Check if request is approved.
     */
    public function isApproved(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED_BY_ICT,
            self::STATUS_APPROVED_BY_DIRECTOR,
        ]);
    }

    /**
     * Check if request is rejected.
     */
    public function isRejected(): bool
    {
        return in_array($this->status, [
            self::STATUS_REJECTED_BY_ICT,
            self::STATUS_REJECTED_BY_DIRECTOR,
        ]);
    }

    /**
     * Check if request is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_ICT_REVIEW, self::STATUS_ICT_REVIEWING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_APPROVED_BY_ICT, self::STATUS_APPROVED_BY_DIRECTOR, self::STATUS_INSPECTION_PASSED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED_BY_ICT, self::STATUS_REJECTED_BY_DIRECTOR, self::STATUS_INSPECTION_FAILED => 'bg-red-100 text-red-800',
            self::STATUS_SENT_TO_DIRECTOR, self::STATUS_SENT_TO_PROCUREMENT, self::STATUS_PROCUREMENT_REVIEWING => 'bg-blue-100 text-blue-800',
            self::STATUS_ORDERED, self::STATUS_RECEIVED => 'bg-purple-100 text-purple-800',
            self::STATUS_UNDER_INSPECTION => 'bg-indigo-100 text-indigo-800',
            self::STATUS_SPECS_REVISION_NEEDED => 'bg-orange-100 text-orange-800',
            self::STATUS_READY_FOR_PURCHASE => 'bg-teal-100 text-teal-800',
            self::STATUS_ASSIGNED_TO_USER, self::STATUS_COMPLETED => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get human-readable status.
     */
    public function getStatusLabel(): string
    {
        return str_replace('_', ' ', ucwords($this->status));
    }
}
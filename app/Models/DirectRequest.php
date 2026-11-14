<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'direct_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'director_id',
        'asset_request_id',
        'subject',
        'description',
        'urgency',
        'category_id',
        'status',
        'director_notes',
        'ict_notes',
        'specifications_added',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created the request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the director/approver of the request
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Get the category of the request
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the asset request linked to this direct request
     */
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class, 'asset_request_id');
    }

    /**
     * Get the workflows for this request
     */
    public function workflows()
    {
        return $this->hasMany(RequestWorkflow::class, 'asset_request_id', 'asset_request_id');
    }

    /**
     * Check if the request has an approver assigned
     */
    public function hasApprover()
    {
        return !is_null($this->director_id);
    }

    /**
     * Check if the request can be edited
     */
    public function isEditable()
    {
        return in_array($this->status, ['pending_director', 'director_rejected']);
    }

    /**
     * Check if the request can be deleted
     */
    public function isDeletable()
    {
        return in_array($this->status, ['pending_director', 'director_rejected']);
    }

    /**
     * Check if the request can be approved
     */
    public function isApprovable()
    {
        return $this->status === 'pending_director';
    }

    /**
     * Check if the request can be forwarded to ICT
     */
    public function canForwardToICT()
    {
        return $this->status === 'director_approved';
    }

    /**
     * Check if the request can have specifications added
     */
    public function canAddSpecifications()
    {
        return $this->status === 'sent_to_ict';
    }

    /**
     * Check if the request can be forwarded to procurement
     */
    public function canForwardToProcurement()
    {
        return $this->status === 'ict_specified';
    }

    /**
     * Check if the request can be merged
     */
    public function canMerge()
    {
        return $this->status === 'ict_specified';
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending_director' => 'Pending Approval',
            'director_approved' => 'Approved',
            'director_rejected' => 'Rejected',
            'sent_to_ict' => 'With ICT',
            'ict_specified' => 'ICT Specified',
            'director_sent_to_procurement' => 'Sent to Procurement',
            'merged_to_request' => 'Merged',
            'completed' => 'Completed',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get the urgency label
     */
    public function getUrgencyLabelAttribute()
    {
        $labels = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];

        return $labels[$this->urgency] ?? $this->urgency;
    }

    /**
     * Get the urgency color
     */
    public function getUrgencyColorAttribute()
    {
        $colors = [
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'critical' => 'danger',
        ];

        return $colors[$this->urgency] ?? 'secondary';
    }

    /**
     * Get the status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending_director' => 'warning',
            'director_approved' => 'success',
            'director_rejected' => 'danger',
            'sent_to_ict' => 'primary',
            'ict_specified' => 'info',
            'director_sent_to_procurement' => 'primary',
            'merged_to_request' => 'secondary',
            'completed' => 'success',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope a query to only include pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending_director');
    }

    /**
     * Scope a query to only include approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'director_approved');
    }

    /**
     * Scope a query to only include requests with a specific urgency
     */
    public function scopeUrgency($query, $urgency)
    {
        return $query->where('urgency', $urgency);
    }

    /**
     * Scope a query to only include requests for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include requests for a specific category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include active requests (not deleted)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope a query to only include requests with an approver assigned
     */
    public function scopeHasApprover($query)
    {
        return $query->whereNotNull('director_id');
    }

    /**
     * Scope a query to only include requests without an approver assigned
     */
    public function scopeWithoutApprover($query)
    {
        return $query->whereNull('director_id');
    }
}
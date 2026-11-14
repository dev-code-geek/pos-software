<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestWorkflow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_request_id',
        'actor_id',
        'from_department',
        'to_department',
        'action',
        'status_before',
        'status_after',
        'comments',
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
        ];
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the asset request for this workflow.
     */
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    /**
     * Get the actor (user) who performed the action.
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get human-readable from department.
     */
    public function getFromDepartmentLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->from_department));
    }

    /**
     * Get human-readable to department.
     */
    public function getToDepartmentLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->to_department));
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Auto-generate slug when name is set
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = $category->generateSlug($category->name);
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = $category->generateSlug($category->name);
            }
        });
    }

    public function generateSlug($name)
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    // Relationships (unchanged)
    public function products() { return $this->hasMany(Product::class); }
    public function assetRequests() { return $this->hasMany(AssetRequest::class); }
    public function directRequests() { return $this->hasMany(DirectRequest::class, 'category_id'); }
}
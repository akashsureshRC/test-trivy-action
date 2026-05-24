<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sic7Code extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'sic7_codes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'description',
        'category',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to search by code or description.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Get all unique categories.
     */
    public static function getCategories()
    {
        return self::distinct('category')
                   ->orderBy('category')
                   ->pluck('category', 'category')
                   ->filter();
    }

    /**
     * Get codes by category with pagination.
     */
    public static function getCodesByCategory($category, $perPage = 15)
    {
        return self::byCategory($category)
                   ->orderBy('code')
                   ->paginate($perPage);
    }
}
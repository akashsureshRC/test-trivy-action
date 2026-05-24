<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country_id', 'status'];
    
    /**
     * Get the country that owns the province
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}

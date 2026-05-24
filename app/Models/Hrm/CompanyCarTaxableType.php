<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCarTaxableType extends Model
{
    use HasFactory;
    protected $fillable = ['tax_type'];
    public function companyCars()
    {
        return $this->hasMany(CompanyCar::class);
    }
}

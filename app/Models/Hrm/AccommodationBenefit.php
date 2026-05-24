<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AccommodationBenefit extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id','amount','term'];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'employee_id', 'employee_id');
    }
}

<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commission extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'employee_id',
        'title',
        'type',
        'amount',
        'workspace',
        'created_by',
        'term'
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }

    public static $commissiontype = [
        '' => 'Select Commission Type',
        'fixed'=>'Fixed',
        'percentage'=> 'Percentage',
        'period'=> 'Period',
    ];

    public static $status =[
        '' => 'Select Status',
        'active'=>'Active',
        'expired'=> 'Expired',
    ];
}

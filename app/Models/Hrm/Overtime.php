<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Overtime extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'employee_id',
        'title',
        'type',
        'number_of_days',
        'hours',
        'rate',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }

    public static $Overtimetype =[
        'fixed'=>'Fixed',
        'percentage'=> 'Percentage',
    ];

    public static $status =[
        '' => 'Select Status',
        'active'=>'Active',
        'expired'=> 'Expired',
    ];
}

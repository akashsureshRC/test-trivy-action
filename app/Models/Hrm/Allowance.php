<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allowance extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'employee_id',
        'allowance_option',
        'title',
        'type',
        'amount',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }
    public static $Allowancetype = [
        'fixed'=>'Fixed',
        'percentage'=> 'Percentage',
    ];
    public function allowance_option()
    {
        return null;
    }

    public function allowanceoption()
    {
        return null;
    }
}

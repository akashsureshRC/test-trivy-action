<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Designation extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'branch_id',
        'department_id',
        'name',
        'workspace',
        'created_by'
    ];

    protected static function newFactory()
    {
        return null; // Factory not migrated
    }

    public function branch(){
        return $this->hasOne(Branch::class,'id','branch_id');
    }
    public function department(){
        return $this->hasOne(Department::class,'id','department_id');
    }
}

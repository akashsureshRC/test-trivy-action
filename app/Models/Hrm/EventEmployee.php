<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventEmployee extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'event_id',
        'employee_id',
        'user_id',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }
}

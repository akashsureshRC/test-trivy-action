<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'occasion',
        'workspace',
        'created_by'
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }
}

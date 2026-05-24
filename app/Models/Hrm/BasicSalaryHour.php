<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BasicSalaryHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'normal_hours',
        'ot_hours',
    ];

    protected static function newFactory()
    {
        return null; // Factory not migrated
    }
}

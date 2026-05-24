<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeavePartial extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_record_id',
        'date',
        'hours',
    ];
    public function leaveRecord()
    {
        return $this->belongsTo(LeaveRecord::class);
    }
}

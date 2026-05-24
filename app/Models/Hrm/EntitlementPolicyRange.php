<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntitlementPolicyRange extends Model
{
    use HasFactory;
    protected $fillable = [
        'entitlement_policy_id',
        'start_date',
        'end_date',
        'status',
    ];
}

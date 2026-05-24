<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterAdminCompany extends Model
{
    protected $table = 'master_admin_companies';

    protected $fillable = [
        'master_admin_id',
        'company_id',
    ];

    /**
     * Get the Master Administrator.
     */
    public function masterAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_admin_id');
    }

    /**
     * Get the Company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}

<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeneralSelfServiceSetting extends Model
{
    use HasFactory;

   
        protected $fillable = [
            'auto_enable',
            'attach_payslips',
            'enable_password_protection',
            'allow_tax_certificates',
            'attach_certificates',
            'disable_leave_requests',
            'disable_info_requests',
        ];

    
   
}

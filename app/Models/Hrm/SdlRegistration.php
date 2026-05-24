<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SdlRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'sdl_regisration',
        'effective_from'
    ];
    
    
}

<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxDirective extends Model
{
    use HasFactory;
    protected $fillable = ['directive_type'];
}

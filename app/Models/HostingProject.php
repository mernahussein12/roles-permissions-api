<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingProject extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar',
        'type',
        'start_date',
        'end_date',
        'summary',
        'cost'
    ];
}

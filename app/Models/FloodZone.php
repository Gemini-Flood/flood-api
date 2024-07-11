<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloodZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'coordinates',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloodReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location',
        'description',
        'latitude',
        'longitude',
        'image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

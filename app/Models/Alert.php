<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'flood_zone_id',
        'title',
        'message',
        'risk_level',
        'expires_at'
    ];

    public function zone()
    {
        return $this->belongsTo(FloodZone::class);
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Akuechler\Geoly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use Geoly;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'latitude',
        'longitude',
        'fcm_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    public static function findUsersWithinRadius($latitude, $longitude)
    {
        $lat = floatval($latitude);
        $long = floatval($longitude);
        $users = self::radius($lat, $long, 10)->get();

        return $users;

        /*

        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(CAST(users.latitude AS DECIMAL(10,8)))) * cos(radians(CAST(users.longitude AS DECIMAL(11,8))) - radians(?)) + sin(radians(?)) * sin(radians(CAST(users.latitude AS DECIMAL(10,8))))))";

        return self::select('users.*')
                ->selectRaw("{$haversine} AS distance", [$lat, $long, $lat])
                ->having('distance', '<', 10)
                ->orderBy('distance')
                ->get(); */
    }
}

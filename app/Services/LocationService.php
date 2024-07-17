<?php

namespace App\Services;

use App\Models\User;

class LocationService
{
    public function getUsersInZone($latitude, $longitude)
    {
        $lat = floatval($latitude);
        $long = floatval($longitude);
        $users = User::selectRaw("id, name, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance", [$long, $lat])
                    ->having('distance', '<', 1500)
                    ->get();

        return $users;
    }
}

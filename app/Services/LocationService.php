<?php

namespace App\Services;

use App\Models\User;

class LocationService
{
    public function getUsersInZone($latitude, $longitude, $radius = 10)
    {
        $haversine = "(6371 * acos(cos(radians($latitude))
                        * cos(radians(users.latitude))
                        * cos(radians(users.longitude)
                        - radians($longitude))
                        + sin(radians($latitude))
                        * sin(radians(users.latitude))))";

        $users = User::select('users.*')
                   ->selectRaw("{$haversine} AS distance")
                   ->having('distance', '<=', $radius)
                   ->get();


        return $users;
    }
}

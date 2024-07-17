<?php

namespace App\Services;

use App\Models\User;

class LocationService
{
    public function getUsersInZone($latitude, $longitude)
    {
        $lat = floatval($latitude);
        $long = floatval($longitude);

        $haversine = "(6371 * acos(cos(radians($lat))
                        * cos(radians(users.latitude))
                        * cos(radians(users.longitude)
                        - radians($long))
                        + sin(radians($lat))
                        * sin(radians(users.latitude))))";
        $ha = "(6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude))))";

        $users = User::select('users.*')
                    ->selectRaw("{$haversine} AS distance")
                    ->having('distance', '<=', 1500)
                    ->get();

        return $users;
    }
}

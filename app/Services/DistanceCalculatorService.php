<?php

namespace App\Services;

class DistanceCalculatorService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function kmBetween($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadiusKm = 6371;

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

}

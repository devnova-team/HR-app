<?php

namespace App\Support;

class GeoDistance
{

    public static function Calculate(float $lat1, float $lon1, float $lat2, float $lon2): float
    {

        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }



    public static function isWithinRadius(float $lat1, float $lon1, float $lat2, float $lon2, float $maxRadius): bool
    {
        $distance = self::calculate($lat1, $lon1, $lat2, $lon2);

        return $distance <= $maxRadius;
    }
}

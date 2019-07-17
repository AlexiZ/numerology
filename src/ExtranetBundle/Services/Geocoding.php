<?php

namespace ExtranetBundle\Services;

class Geocoding
{
    /**
     * Convert decimal coordinates as degrees.
     *
     * @param string $coordinates
     * @param bool $asArray
     *
     * @return array|string
     */
    public function DECtoDMS($coordinates, $asArray = false)
    {
        $vars = explode(".", $coordinates);
        $deg = (int) $vars[0];
        $tempma = "0." . $vars[1];

        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = round($tempma - ($min * 60), 0);

        return $asArray ? [$deg, $min, $sec] : $deg . $min . $sec;
    }

    /**
     * Convert degree coordinates as decimals.
     *
     * @param array $coordinates
     *
     * @return float|int|mixed
     */
    public function DMStoDEC($coordinates)
    {
        return $coordinates[0] + ((($coordinates[1] * 60) + ($coordinates[2])) / 3600);
    }
}
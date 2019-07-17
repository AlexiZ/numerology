<?php

namespace ExtranetBundle\Services;

class Geocoding
{
    public function DECtoDMS($dec, $asArray = false)
    {
        $vars = explode(".", $dec);
        $deg = (int) $vars[0];
        $tempma = "0." . $vars[1];

        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = round($tempma - ($min * 60), 0);

        return $asArray ? [$deg, $min, $sec] : $deg . $min . $sec;
    }
}
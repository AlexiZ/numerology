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

    /**
     * Find timezone associated to given decimal coordinates
     *
     * @param $currentLatitude
     * @param $currentLongitude
     * @param string $countryCode
     *
     * @return mixed|string
     */
    public function getNearestTimezone($currentLatitude, $currentLongitude, $countryCode = '') {
        $timezone_ids = ($countryCode) ? \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $countryCode) : \DateTimeZone::listIdentifiers();

        if ($timezone_ids && is_array($timezone_ids) && isset($timezone_ids[0])) {
            $time_zone = '';
            $tz_distance = 0;

            //only one identifier?
            if (count($timezone_ids) == 1) {
                $time_zone = $timezone_ids[0];
            } else {
                foreach ($timezone_ids as $timezone_id) {
                    $timezone = new \DateTimeZone($timezone_id);
                    $location = $timezone->getLocation();
                    $tz_lat   = $location['latitude'];
                    $tz_long  = $location['longitude'];

                    $theta    = $currentLongitude - $tz_long;
                    $distance = (sin(deg2rad($currentLatitude)) * sin(deg2rad($tz_lat))) + (cos(deg2rad($currentLatitude)) * cos(deg2rad($tz_lat)) * cos(deg2rad($theta)));
                    $distance = acos($distance);
                    $distance = abs(rad2deg($distance));
                    // echo '<br />'.$timezone_id.' '.$distance;

                    if (!$time_zone || $tz_distance > $distance) {
                        $time_zone   = $timezone_id;
                        $tz_distance = $distance;
                    }
                }
            }
            return  $time_zone;
        }
        return 'unknown';
    }
}
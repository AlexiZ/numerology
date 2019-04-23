<?php

namespace ExtranetBundle\Services;

class Geocoding
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getGeolocation($address, $asArray = false)
    {
        $coordinates = null;
        try {
            $result = $this->callGetApi('http://www.mapquestapi.com/geocoding/v1/address', [
                'location' => $address,
                'key' => $this->apiKey,
            ]);

            if (0 === $result['info']['statuscode']) {
                $lat = $this->DECtoDMS($result['results'][0]['locations'][0]['latLng']['lat'], $asArray);
                $lng = $this->DECtoDMS($result['results'][0]['locations'][0]['latLng']['lng'], $asArray);

                if ($asArray) {
                    $coordinates = [
                        'lat' => $lat,
                        'lng' => $lng,
                    ];
                } else {
                    $coordinates = $lat . ',' . $lng;
                }
            }
        } catch (\Exception $e) {}

        return $coordinates;
    }

    protected function DECtoDMS($dec, $asArray = false)
    {
        $vars = explode(".", $dec);
        $deg = (int) $vars[0];
        $tempma = "0." . $vars[1];

        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = round($tempma - ($min * 60), 0);

        return $asArray ? [$deg, $min, $sec] : $deg . $min . $sec;
    }

    private function callGetApi($url, array $parameters = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url . (!empty($parameters) ? '?' . http_build_query($parameters) : ''));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json, charset=utf8'
        ]);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        return json_decode($output, true);
    }
}
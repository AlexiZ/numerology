<?php

namespace AppBundle\Twig;

use AppBundle\Services\Slack\SlackManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GeocodingExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('coordsToText', array($this, 'getCoordsToText')),
        );
    }

    public function getCoordsToText($coordinates)
    {
        return $coordinates['lat'][0] . '° ' . $coordinates['lat'][1] . '\' ' . $coordinates['lat'][2] . '\'\', '
            . $coordinates['lng'][0] . '° ' . $coordinates['lng'][1] . '\' ' . $coordinates['lng'][2] . '\'\''
        ;
    }
}
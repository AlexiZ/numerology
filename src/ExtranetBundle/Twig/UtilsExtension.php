<?php

namespace ExtranetBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UtilsExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('natJoin', array($this, 'natJoin')),
        );
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('fileExists', array($this, 'fileExists')),
        ];
    }

    public function natJoin($array, $delimiter = ', ')
    {
        $array = array_values($array);
        if (0 === count($array)) {
            return '';
        }
        if (1 === count($array)) {
            return $array[0];
        }

        $string = $array[0];

        for ($i = 1; $i < count($array) - 1; $i++) {
            $string .= $delimiter. $array[$i];
        }

        return $string . ' et ' . $array[count($array) - 1];
    }

    public function fileExists($file)
    {
        return file_exists($file);
    }
}
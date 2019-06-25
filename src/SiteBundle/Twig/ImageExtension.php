<?php

namespace SiteBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    protected $documentRoot;
    protected $router;

    public function __construct(RouterInterface $router, $documentRoot)
    {
        $this->documentRoot = $documentRoot;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('imgPath', [$this, 'getImgPath']),
        );
    }

    public function getImgPath($filename, $dimension, $outbound = false, $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($width, $height) = explode('x', $dimension);

        $mode = $outbound ? 'o' : 'i';

        return $this->router->generate('site_media_preview', [
            'path' => $filename,
            'width' => $width,
            'height' => $height,
            'mode' => $mode,
        ], $absolute);
    }
}

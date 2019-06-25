<?php

namespace SiteBundle\Controller;

use DateTime;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function resizeAction(Request $request, $path, $width, $height, $mode = 'i')
    {
        while (false !== strpos($path, '%')) {
            $path = rawurldecode($path);
        }

        if ('/' !== $path{0}) {
            $path = '/'. $path;
        }

        $absPath = $this->getParameter('kernel.project_dir').'/web'.$path;

        if (!$absPath = realpath($absPath)) {
            throw $this->createNotFoundException();
        }

        $lastModified = DateTime::createFromFormat('U', filemtime($absPath));
        $imageChecksum = sha1_file($absPath);

        $response = new Response();
        $response->setPublic();
        $response->setETag($imageChecksum);
        $response->setLastModified($lastModified);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $imagine = new Imagine();
        $size    = new Box($width, $height);
        $mode = 'o' === $mode ?
            ImageInterface::THUMBNAIL_OUTBOUND :
            ImageInterface::THUMBNAIL_INSET
        ;

        $image = $imagine->open($absPath);

        $cacheFile = $this->getParameter('kernel.project_dir').'/web'. urldecode($request->getRequestUri('SCRIPT_NAME'));

        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0777, true);
        }

        $image
            ->thumbnail($size, $mode)
            ->save($cacheFile, ['quality' => 90])
        ;

        $response = new BinaryFileResponse($cacheFile);

        $response->setPublic();
        $response->setETag($imageChecksum);
        $response->setLastModified($lastModified);

        return $response;
    }
}

<?php
namespace ExtranetBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Router;

class AccessDeniedListener
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onAccessDeniedException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof AccessDeniedHttpException) {
            $event->setResponse(new RedirectResponse($this->router->generate('extranet_index')));
        }
    }
}
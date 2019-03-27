<?php

namespace AppBundle\Controller;

use AppBundle\Auth0\Auth0Manager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminController extends Controller
{
    public function indexAction(Auth0Manager $auth0Manager)
    {
        $users = $auth0Manager->getUsers();

        return $this->render('@App/Admin/index.html.twig', ['users' => $users]);
    }

    public function userValidateAction(Auth0Manager $auth0Manager, $userId)
    {
        return new JsonResponse($auth0Manager->grantRole($userId, 'ROLE_USER'));
    }

    public function userRefuseAction(Auth0Manager $auth0Manager, $userId)
    {
        return new JsonResponse($auth0Manager->removeRole($userId, 'ROLE_USER'));
    }
}

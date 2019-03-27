<?php

namespace AppBundle\Controller;

use AppBundle\Auth0\Auth0Manager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
    public function indexAction(Auth0Manager $auth0Manager)
    {
        $users = $auth0Manager->getUsers();

        return $this->render('@App/Admin/index.html.twig', ['users' => $users]);
    }
}

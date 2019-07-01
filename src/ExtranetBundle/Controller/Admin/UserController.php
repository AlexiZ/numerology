<?php

namespace ExtranetBundle\Controller\Admin;

use ExtranetBundle\Form\UserType;
use ExtranetBundle\Services\Auth0\Auth0Manager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function usersListAction(Auth0Manager $auth0Manager)
    {
        $users = $auth0Manager->getUsers();

        return $this->render('@Extranet/Admin/User/index.html.twig', ['users' => $users]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function userValidateAction(Auth0Manager $auth0Manager, $userId)
    {
        return new JsonResponse($auth0Manager->grantRole($userId, 'ROLE_USER'));
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function userRefuseAction(Auth0Manager $auth0Manager, $userId)
    {
        return new JsonResponse($auth0Manager->removeRole($userId, 'ROLE_USER'));
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function userAddAction(Request $request, Auth0Manager $auth0Manager)
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $auth0Manager->createUser([
                'email' => $form->get('email')->getData(),
                'password' => $form->get('password')->getData(),
                'slackId' => $form->get('slackId')->getData(),
            ]);

            if ($user) {
                return $this->redirectToRoute('admin_users_list');
            }
        }

        $content = $this->render('@Extranet/Admin/User/_add.html.twig', [
            'form' => $form->createView(),
            'baseEmailParts' => explode('@', $this->getParameter('contact.email')),
        ]);

        return new JsonResponse($content->getContent());
    }
}

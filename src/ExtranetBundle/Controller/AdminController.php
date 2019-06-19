<?php

namespace ExtranetBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Definition;
use ExtranetBundle\Entity\Number;
use ExtranetBundle\Form\DefinitionType;
use ExtranetBundle\Form\NumberType;
use ExtranetBundle\Form\UserType;
use ExtranetBundle\Services\Auth0\Auth0Manager;
use ExtranetBundle\Services\Slack\SlackManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Extranet/Admin/index.html.twig');
    }

    /** USER */

    public function usersListAction(Auth0Manager $auth0Manager)
    {
        $users = $auth0Manager->getUsers();

        return $this->render('@Extranet/Admin/User/index.html.twig', ['users' => $users]);
    }

    public function userValidateAction(Auth0Manager $auth0Manager, $userId)
    {
        return new JsonResponse($auth0Manager->grantRole($userId, 'ROLE_USER'));
    }

    public function userRefuseAction(Auth0Manager $auth0Manager, $userId)
    {
        return new JsonResponse($auth0Manager->removeRole($userId, 'ROLE_USER'));
    }

    public function userAddAction(Request $request, Auth0Manager $auth0Manager)
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $auth0Manager->createUser([
                'email' => $form->get('email')->getData(),
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

    /** NUMBER */

    public function numbersListAction(ManagerRegistry $registry)
    {
        $numbers = $registry->getRepository(Number::class)->findAll();

        return $this->render('@Extranet/Admin/Number/index.html.twig', ['numbers' => $numbers]);
    }

    public function numberEditAction($id, Request $request, ManagerRegistry $registry)
    {
        $number = $registry->getRepository(Number::class)->findOneById($id);
        if (!$number) {
            return $this->redirectToRoute('admin_numbers_list');
        }

        $form = $this->createForm(NumberType::class, $number);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registry->getManager()->persist($number);
            $registry->getManager()->flush();
        }

        return $this->render('@Extranet/Admin/Number/edit.html.twig', [
            'number' => $number,
            'form' => $form->createView(),
        ]);
    }

    /** DEFINITION */

    public function definitionsListAction(ManagerRegistry $registry)
    {
        $definitions = $registry->getRepository(Definition::class)->findAll();

        return $this->render('@Extranet/Admin/Definition/index.html.twig', ['definitions' => $definitions]);
    }

    public function definitionEditAction($id, Request $request, ManagerRegistry $registry)
    {
        $definition = $registry->getRepository(Definition::class)->findOneById($id);
        if (!$definition) {
            return $this->redirectToRoute('admin_definitions_list');
        }

        $form = $this->createForm(DefinitionType::class, $definition);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registry->getManager()->persist($definition);
            $registry->getManager()->flush();
        }

        return $this->render('@Extranet/Admin/Definition/edit.html.twig', [
            'definition' => $definition,
            'form' => $form->createView(),
        ]);
    }
}

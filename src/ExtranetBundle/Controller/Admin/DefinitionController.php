<?php

namespace ExtranetBundle\Controller\Admin;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Definition;
use ExtranetBundle\Form\DefinitionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefinitionController extends Controller
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function definitionsListAction(ManagerRegistry $registry)
    {
        $definitions = $registry->getRepository(Definition::class)->findAll();

        return $this->render('@Extranet/Admin/Definition/index.html.twig', ['definitions' => $definitions]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
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

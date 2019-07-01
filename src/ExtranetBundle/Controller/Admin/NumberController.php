<?php

namespace ExtranetBundle\Controller\Admin;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Number;
use ExtranetBundle\Form\NumberType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NumberController extends Controller
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function numbersListAction(ManagerRegistry $registry)
    {
        $numbers = $registry->getRepository(Number::class)->findAll();

        return $this->render('@Extranet/Admin/Number/index.html.twig', ['numbers' => $numbers]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
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
}

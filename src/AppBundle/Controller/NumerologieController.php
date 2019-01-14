<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Numerologie;
use AppBundle\Form\NumerologieType;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NumerologieController extends Controller
{
    public function indexAction(Request $request)
    {
        $parameters = [];
        return $this->render('@App/Numerologie/index.html.twig', $parameters);
    }

    public function addAction(Request $request)
    {
        $parameters = [];
        $numerologie = new Numerologie();
        $form = $this->createForm(NumerologieType::class, $numerologie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('numerologie_show', ['numerologie' => array_filter($numerologie->serialize())]);
        }

        $parameters = array_merge($parameters, [
            'form' => $form->createView()
        ]);

        return $this->render('@App/Numerologie/add.html.twig', $parameters);
    }

    public function showAction(Request $request, ManagerRegistry $managerRegistry)
    {
        if (!$request->query->has('numerologie')) {
            return $this->redirectToRoute('numerologie_add');
        }

        $numerologie = new Numerologie();
        $numerologie->unserialize($request->get('numerologie'));

        return $this->render('@App/Numerologie/show.html.twig', ['subject' => $numerologie]);
    }
}

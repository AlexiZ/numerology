<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Numerologie;
use AppBundle\Services\Numerologie as NumerologieService;
use AppBundle\Form\NumerologieType;
use AppBundle\Services\JsonIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NumerologieController extends Controller
{
    public function indexAction(JsonIO $jsonIO)
    {
        return $this->render('@App/Numerologie/index.html.twig', [
            'histories' => $jsonIO->readHistoryFolder(),
        ]);
    }

    public function addAction(Request $request, JsonIO $jsonIO, NumerologieService $numerologieService)
    {
        $parameters = [];
        $subject = new Numerologie();
        $form = $this->createForm(NumerologieType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $jsonIO->writeNumerology($subject, $numerologieService->exportData($subject));

            return $this->redirectToRoute('numerologie_show', ['filename' => $filename]);
        }

        $parameters = array_merge($parameters, [
            'form' => $form->createView()
        ]);

        return $this->render('@App/Numerologie/add.html.twig', $parameters);
    }

    public function showAction(Request $request, NumerologieService $numerologieService)
    {
        if (!$request->query->has('filename')) {
            return $this->redirectToRoute('numerologie_add');
        }

        $numerologie = $numerologieService->getSubject($request->get('filename'));

        if (!$numerologie) {
            return $this->redirectToRoute('numerologie_index');
        }

        return $this->render('@App/Numerologie/show.html.twig', ['subject' => $numerologie]);
    }
}

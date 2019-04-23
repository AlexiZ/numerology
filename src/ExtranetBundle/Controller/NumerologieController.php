<?php

namespace ExtranetBundle\Controller;

use ExtranetBundle\Entity\Numerologie;
use ExtranetBundle\Services\Numerologie as NumerologieService;
use ExtranetBundle\Form\NumerologieType;
use ExtranetBundle\Services\JsonIO;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NumerologieController extends Controller
{
    public function indexAction(JsonIO $jsonIO)
    {
        return $this->render('@Extranet/Numerologie/index.html.twig', [
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

        return $this->render('@Extranet/Numerologie/add.html.twig', $parameters);
    }

    public function editAction(Request $request, JsonIO $jsonIO, NumerologieService $numerologieService, $md5)
    {
        $parameters = [];
        $subject = $numerologieService->getSubject($md5);
        if (!$subject) {
            return $this->redirectToRoute('numerologie_add');
        }

        $form = $this->createForm(NumerologieType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $jsonIO->writeNumerology($subject, $numerologieService->exportData($subject));

            return $this->redirectToRoute('numerologie_show', ['filename' => $filename]);
        }

        $parameters = array_merge($parameters, [
            'form' => $form->createView()
        ]);

        return $this->render('@Extranet/Numerologie/edit.html.twig', $parameters);
    }

    public function supprimerAction(JsonIO $jsonIO, $md5)
    {
        $jsonIO->deleteJson($md5);

        return $this->redirectToRoute('numerologie_index');
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

        return $this->render('@Extranet/Numerologie/show.html.twig', [
            'subject' => $numerologie,
            'filename' => $request->get('filename')
        ]);
    }

    public function exportPdfAction(NumerologieService $numerologieService, $md5)
    {
        $numerologie = $numerologieService->getSubject($md5);
        $html = $this->renderView('@Extranet/Numerologie/export.pdf.twig', [
            'subject' => $numerologie,
            'filename' => $md5,
        ]);

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            uniqid() . '.pdf'
        );
    }
}

<?php

namespace SiteBundle\Controller;

use ExtranetBundle\Entity\Numerologie;
use ExtranetBundle\Services\Numerologie as NumerologieService;
use ExtranetBundle\Form\NumerologieType;
use ExtranetBundle\Services\JsonIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function homepageAction()
    {
        return $this->render('SiteBundle:Default:index.html.twig');
    }

    public function tryAction(Request $request, JsonIO $jsonIO, NumerologieService $numerologieService)
    {
        $subject = new Numerologie();
        $form = $this->createForm(NumerologieType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $jsonIO->writeNumerology($subject, $numerologieService->exportData($subject));

            return $this->redirectToRoute('site_show', ['filename' => $filename]);
        }

        return $this->render('SiteBundle:Default:try.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function showAction(Request $request, NumerologieService $numerologieService)
    {
        if (!$request->query->has('filename')) {
            return $this->redirectToRoute('site_try');
        }

        $numerologie = $numerologieService->getSubject($request->get('filename'));

        if (!$numerologie) {
            return $this->redirectToRoute('site_homepage');
        }

        return $this->render('@Site/Default/show.html.twig', [
            'subject' => $numerologie,
            'filename' => $request->get('filename')
        ]);
    }
}

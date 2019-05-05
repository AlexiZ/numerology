<?php

namespace ExtranetBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Services\Numerologie;
use ExtranetBundle\Form\NumerologieType;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AnalysisController extends Controller
{
    const ROUTE_SHOW = 'extranet_show';
    const ROUTE_INDEX = 'extranet_index';
    const SUBJECT = 'subject';

    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository(Analysis::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            $history = $repository->getAllUsersHistory();
        } else {
            $history = $repository->getSingleUserHistory($this->getUser()->getId());
        }

        return $this->render('@Extranet/Analysis/index.html.twig', [
            'subjects' => $history,
        ]);
    }

    public function addAction(Request $request, ManagerRegistry $registry, Numerologie $numerologie)
    {
        $parameters = [];
        $subject = new Analysis();
        $form = $this->createForm(NumerologieType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $subject->setData($numerologie->exportData($subject));
            $subject->setUserId($this->getUser()->getId());
            $subject->setLevel(Analysis::LEVEL_PREMIUM);

            $registry->getManager()->persist($subject);
            $registry->getManager()->flush();

            return $this->redirectToRoute(self::ROUTE_SHOW, ['hash' => $subject->getHash()]);
        }

        $parameters = array_merge($parameters, [
            'form' => $form->createView()
        ]);

        return $this->render('@Extranet/Analysis/add.html.twig', $parameters);
    }

    public function editAction($hash, Request $request, Numerologie $numerologieService, ManagerRegistry $registry)
    {
        $parameters = [];
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_DELETED === $subject->getStatus()) {
            return $this->redirectToRoute('extranet_add');
        }

        $form = $this->createForm(NumerologieType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $subject->setData($numerologieService->exportData($subject));

            $registry->getManager()->persist($subject);
            $registry->getManager()->flush();

            return $this->redirectToRoute(self::ROUTE_SHOW, ['hash' => $subject->getHash()]);
        }

        $parameters = array_merge($parameters, [
            'form' => $form->createView(),
            self::SUBJECT => $subject,
        ]);

        return $this->render('@Extranet/Analysis/edit.html.twig', $parameters);
    }

    public function supprimerAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject) {
            return $this->redirectToRoute(self::ROUTE_INDEX);
        }

        $subject->setStatus(Analysis::STATUS_DELETED);

        $registry->getManager()->persist($subject);
        $registry->getManager()->flush();

        return $this->redirectToRoute(self::ROUTE_INDEX);
    }

    public function showAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_DELETED === $subject->getStatus()) {
            return $this->redirectToRoute(self::ROUTE_INDEX);
        }

        return $this->render('@Extranet/Analysis/show.html.twig', [
            self::SUBJECT => $subject,
        ]);
    }

    public function exportPdfAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_DELETED === $subject->getStatus() || Analysis::LEVEL_FREE === $subject->getLevel()) {
            return $this->redirectToRoute(self::ROUTE_SHOW, ['hash' => $hash]);
        }

        $html = $this->renderView('@Extranet/Analysis/export.pdf.twig', [
            self::SUBJECT => $subject,
        ]);

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            uniqid() . '.pdf'
        );
    }
}

<?php

namespace ExtranetBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Services\Numerologie;
use ExtranetBundle\Form\AnalysisType;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

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
        $form = $this->createForm(AnalysisType::class, $subject);

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

        $form = $this->createForm(AnalysisType::class, $subject);

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

    public function showAction($hash, ManagerRegistry $registry, Numerologie $numerologieService)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_DELETED === $subject->getStatus()) {
            return $this->redirectToRoute(self::ROUTE_INDEX);
        }

        return $this->render('@Extranet/Analysis/show.html.twig', [
            self::SUBJECT => $subject,
            'identity' => $numerologieService->getIdentityDetails($subject),
            'lettersChartValues' => $numerologieService->getLettersChartValues($subject),
            'lettersDifferences' => $numerologieService->getLettersDifferences($subject),
            'lettersSynthesis' => $numerologieService->getLettersSynthesis($subject),
        ]);
    }

    public function listComparisonsAction($hash, ManagerRegistry $registry)
    {
        $subjects = $registry->getRepository(Analysis::class)->getSingleUserHistory($this->getUser()->getId());
        $source = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        return new JsonResponse($this->render('@Extranet/Analysis/_list_comparisons.html.twig', [
            'subjects' => $subjects,
            'source' => $source,
        ])->getContent());
    }

    public function compareAction($hash1, $hash2, ManagerRegistry $registry)
    {
        /** @var Analysis[] $subjects */
        $subjects = $registry->getRepository(Analysis::class)->findByHash([$hash1, $hash2]);

        if (2 !== count($subjects) || Analysis::STATUS_DELETED === $subjects[0]->getStatus() || Analysis::STATUS_DELETED === $subjects[1]->getStatus()) {
            return $this->redirectToRoute(self::ROUTE_INDEX);
        }

        return $this->render('@Extranet/Analysis/compare.html.twig', [
            'subjects' => $subjects,
        ]);
    }
}

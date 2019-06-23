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

    public function showAction($hash, ManagerRegistry $registry, Numerologie $numerologieService, TranslatorInterface $translator)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_DELETED === $subject->getStatus()) {
            return $this->redirectToRoute(self::ROUTE_INDEX);
        }

        $a = [];
        $b = [
            $numerologieService->getGlobalNumber($subject) => 'global',
            $numerologieService->getInheritedNumber($subject) => 'inherited',
            $numerologieService->getDutyNumber($subject) => 'duty',
            $numerologieService->getSocialNumber($subject) => 'social',
            $numerologieService->getStructureNumber($subject) => 'structure',
        ];
        $missing = array_values($numerologieService->getMissingLettersNumbers(str_replace(' ', '', $subject->getFullNames())));
        $c = [
            'strong' => array_values($numerologieService->getStrongLettersNumbers(str_replace(' ', '', $subject->getFullNames()))),
            'missingIn' => array_values(array_intersect(array_keys($b), $missing)),
            'missingOut' => array_values(array_diff($missing, array_keys($b))),
            'weak' => array_values($numerologieService->getWeakLettersNumbers(str_replace(' ', '', $subject->getFullNames()))),
        ];

        foreach ($c as $i => $j) {
            if (!isset($a[$i])) {
                $a[$i] = [];
            }

            foreach ($j as $k) {
                if (isset($b[$k])) {
                    $a[$i][$k] = $b[$k];
                }
            }
        }

        $d = [];
        foreach ($a as $l => $m) {
            foreach ($m as $n => $o) {
                $d[$l][$n] = $translator->trans('analysis.show.vibrations.identity.' . $o);
            }
        }

        return $this->render('@Extranet/Analysis/show.html.twig', [
            self::SUBJECT => $subject,
            'identity' => $d,
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

    public function exportPdfAction($hash, Request $request, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_ACTIVE !== $subject->getStatus() || Analysis::LEVEL_PREMIUM !== $subject->getLevel()) {
            return $this->redirectToRoute(self::ROUTE_SHOW, ['hash' => $hash]);
        }

        $header = $this->renderView('@Site/PDF/header.html.twig', [
            self::SUBJECT => $subject,
            'path' => rtrim($request->server->get('DOCUMENT_ROOT'), "/"),
        ]);
        $html = $this->renderView('@Site/PDF/content.html.twig', [
            self::SUBJECT => $subject,
            'path' => rtrim($request->server->get('DOCUMENT_ROOT'), "/"),
        ]);
        $footer = $this->renderView('@Site/PDF/footer.html.twig');

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, [
                'header-html' => $header,
                'footer-html' => $footer,
            ]),
            $subject->getPublicName() . '.pdf'
        );
    }
}

<?php

namespace SiteBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Entity\Definition;
use ExtranetBundle\Entity\Number;
use ExtranetBundle\Form\AnalysisType;
use ExtranetBundle\Services\Numerologie;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AnalysisController extends Controller
{
    /**
     * @var Numerologie
     */
    private $numerologieService;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(Numerologie $numerologieService, ManagerRegistry $registry)
    {
        $this->numerologieService = $numerologieService;
        $this->registry = $registry;
    }

    public function tryAction($version, Request $request)
    {
        $subject = new Analysis();
        $form = $this->createForm(AnalysisType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $subject->setData($this->numerologieService->exportData($subject));
            $subject->setUserId($this->getUser() ? $this->getUser()->getId() : null);
            $subject->setLevel($version);

            if (Analysis::LEVEL_PREMIUM === $version) {
                $subject->setStatus(Analysis::STATUS_PENDING);

                $this->registry->getManager()->persist($subject);
                $this->registry->getManager()->flush();

                return $this->redirectToRoute('site_payment', ['hash' => $subject->getHash()]);
            }

            $this->registry->getManager()->persist($subject);
            $this->registry->getManager()->flush();

            return $this->redirectToRoute('site_show', ['hash' => $subject->getHash()]);
        }

        return $this->render('SiteBundle:Default:try.html.twig', [
            'form' => $form->createView(),
            'version' => $version,
        ]);
    }

    public function showAction($hash, Numerologie $numerologieService)
    {
        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_ACTIVE !== $subject->getStatus()) {
            return $this->redirectToRoute('site_homepage');
        }

        return $this->render('@Site/Default/show.html.twig', [
            'subject' => $subject,
            'identity' => $numerologieService->getIdentityDetails($subject),
            'lettersChartValues' => $numerologieService->getLettersChartValues($subject),
            'lettersDifferences' => $numerologieService->getLettersDifferences($subject),
            'lettersSynthesis' => $numerologieService->getLettersSynthesis($subject),
        ]);
    }

    public function showDetailsAction($hash, Request $request)
    {
        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject) {
            return new JsonResponse(null, 404);
        }

        $details = [];
        if ($request->query->has('definition')) {
            /** @var Definition $definition */
            $definition = $this->registry->getRepository(Definition::class)->findOneByName($request->query->get('definition'));

            if ($definition) {
                $details['definition'] = $definition->getContent();
            }

            if ($request->query->has('value')) {
                try {
                    /** @var Number $value */
                    $value = $this->registry->getRepository(Number::class)->findOneByValue($request->query->get('value'));

                    if ($value) {
                        $details['value'] = $value->get($request->query->get('definition'));
                    }
                } catch (\Exception $e) {}
            }
        }

        return new JsonResponse($details);
    }

    public function exportPdfAction($hash)
    {
        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_ACTIVE !== $subject->getStatus() || Analysis::LEVEL_FREE === $subject->getLevel()) {
            return $this->redirectToRoute('site_show', ['hash' => $hash]);
        }

        $html = $this->renderView('@Site/Default/export.pdf.twig', [
            'subject' => $subject,
        ]);

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            $subject->getPublicName() . '.pdf'
        );
    }
}

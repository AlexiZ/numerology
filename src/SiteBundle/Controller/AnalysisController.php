<?php

namespace SiteBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Entity\Definition;
use ExtranetBundle\Entity\Number;
use ExtranetBundle\Form\AnalysisType;
use ExtranetBundle\Services\Numerologie;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use ReCaptcha\ReCaptcha;
use SiteBundle\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AnalysisController extends Controller
{
    const SUBJECT = 'subject';

    /**
     * @var Numerologie
     */
    private $numerologieService;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    public function __construct(Numerologie $numerologieService, ManagerRegistry $registry, \Swift_Mailer $mailer)
    {
        $this->numerologieService = $numerologieService;
        $this->registry = $registry;
        $this->mailer = $mailer;
    }

    public function tryAction($version, Request $request)
    {
        $subject = new Analysis();
        $form = $this->createForm(AnalysisType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recaptcha = new ReCaptcha($this->getParameter('google_recaptcha.secret_key'));
            $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

            if (!$resp->isSuccess()) {
                $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'envoi de votre message.');
            } else {
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
        }

        return $this->render('SiteBundle:Default:try.html.twig', [
            'form' => $form->createView(),
            'version' => $version,
        ]);
    }

    public function showAction($hash, Request $request)
    {
        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject) {
            return $this->redirectToRoute('site_homepage');
        }

        $subject->setData($this->numerologieService->exportData($subject));
        $subject->setUpdatedAt(new \DateTime());
        $this->registry->getManager()->persist($subject);
        $this->registry->getManager()->flush();

        if (Analysis::STATUS_ACTIVE !== $subject->getStatus() && !(Analysis::STATUS_PENDING === $subject->getStatus() && Analysis::LEVEL_FREE === $subject->getLevel())) {
            $subject->setLevel(Analysis::LEVEL_FREE);
        }

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recaptcha = new ReCaptcha($this->getParameter('google_recaptcha.secret_key'));
            $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

            if (!$resp->isSuccess()) {
                $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'envoi de votre message.');
            } else {
                $message = (new \Swift_Message('Nouveau commentaire'))
                    ->setFrom($this->getParameter('contact.from'))
                    ->setTo($this->getParameter('contact.email'))
                    ->setBody(
                        $this->renderView(
                            '@Site/Emails/contact.html.twig',
                            [
                                'hash' => $subject->getHash(),
                                'message' => $form->getData(),
                            ]
                        ),
                        'text/html'
                    );

                try {
                    $this->mailer->send($message);

                    $this->addFlash('success', 'Votre message a bien été envoyé');
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'envoi de votre message.');
                }
            }

            return $this->redirectToRoute('site_show', ['hash' => $hash]);
        }

        return $this->render('@Site/Default/show.html.twig', [
            'subject' => $subject,
            'identity' => $this->numerologieService->getIdentityDetails($subject),
            'lettersChartValues' => $this->numerologieService->getLettersChartValues($subject),
            'lettersDifferences' => $this->numerologieService->getLettersDifferences($subject),
            'lettersSynthesis' => $this->numerologieService->getLettersSynthesis($subject),
            'form' => $form->createView(),
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

    public function exportPdfAction($hash, Request $request, ManagerRegistry $registry, Numerologie $numerologieService)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_ACTIVE !== $subject->getStatus() || Analysis::LEVEL_PREMIUM !== $subject->getLevel()) {
            return $this->redirectToRoute('site_show', ['hash' => $hash]);
        }

        $header = $this->renderView('@Site/PDF/header.html.twig', [
            self::SUBJECT => $subject,
            'path' => rtrim($request->server->get('DOCUMENT_ROOT'), "/"),
        ]);
        $html = $this->renderView('@Site/PDF/content.html.twig', [
            self::SUBJECT => $subject,
            'path' => rtrim($request->server->get('DOCUMENT_ROOT'), "/"),
            'identity' => $numerologieService->getIdentityDetails($subject),
            'lettersChartValues' => $numerologieService->getLettersChartValues($subject),
            'lettersDifferences' => $numerologieService->getFullLettersDifferences($subject),
            'lettersSynthesis' => $numerologieService->getLettersSynthesis($subject),
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

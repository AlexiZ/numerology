<?php

namespace SiteBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Entity\Definition;
use ExtranetBundle\Entity\Number;
use ExtranetBundle\Services\Numerologie;
use ExtranetBundle\Form\AnalysisType;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use SiteBundle\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @var Numerologie
     */
    private $numerologieService;

    public function __construct(Numerologie $numerologieService)
    {
        $this->numerologieService = $numerologieService;
    }

    public function homepageAction()
    {
        return $this->render('SiteBundle:Default:index.html.twig');
    }

    public function tryAction($version, Request $request, ManagerRegistry $registry)
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

                $registry->getManager()->persist($subject);
                $registry->getManager()->flush();

                return $this->redirectToRoute('site_payment', ['hash' => $subject->getHash()]);
            }

            $registry->getManager()->persist($subject);
            $registry->getManager()->flush();

            return $this->redirectToRoute('site_show', ['hash' => $subject->getHash()]);
        }

        return $this->render('SiteBundle:Default:try.html.twig', [
            'form' => $form->createView(),
            'version' => $version,
        ]);
    }

    public function paymentAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_PENDING !== $subject->getStatus()) {
            return $this->redirectToRoute('site_homepage');
        }

        return $this->render('SiteBundle:Default:payment.html.twig', [
            'subject' => $subject,
        ]);
    }

    public function paymentConfirmationAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_PENDING !== $subject->getStatus()) {
            return $this->redirectToRoute('site_homepage');
        }

        $subject->setStatus(Analysis::STATUS_ACTIVE);

        $registry->getManager()->persist($subject);
        $registry->getManager()->flush();

        return $this->redirectToRoute('site_show', ['hash' => $hash]);
    }

    public function showAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || Analysis::STATUS_ACTIVE !== $subject->getStatus()) {
            return $this->redirectToRoute('site_homepage');
        }

        return $this->render('@Site/Default/show.html.twig', [
            'subject' => $subject,
        ]);
    }

    public function showDetailsAction($hash, Request $request, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject) {
            return new JsonResponse(null, 404);
        }

        $details = [];
        if ($request->query->has('definition')) {
            /** @var Definition $definition */
            $definition = $registry->getRepository(Definition::class)->findOneByName($request->query->get('definition'));

            if ($definition) {
                $details['definition'] = $definition->getContent();
            }

            if ($request->query->has('value')) {
                try {
                    /** @var Number $value */
                    $value = $registry->getRepository(Number::class)->findOneByValue($request->query->get('value'));

                    if ($value) {
                        $details['value'] = $value->get($request->query->get('definition'));
                    }
                } catch (\Exception $e) {}
            }
        }

        return new JsonResponse($details);
    }

    public function exportPdfAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

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

    public function contactAction(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = (new \Swift_Message('Nouvelle demande de contact'))
                ->setFrom('no-reply@numerologie.com')
                ->setTo($this->getParameter('contact.email'))
                ->setBody(
                    $this->renderView(
                        '@Site/Emails/contact.html.twig',
                        ['message' => $form->getData()]
                    ),
                    'text/html'
                )
            ;

            try {
                $mailer->send($message);

                $this->addFlash('success', 'Votre message a bien été envoyé');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de l\'envoi de votre message.');
            }

            return $this->redirectToRoute('site_contact');
        }

        return $this->render('@Site/Default/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

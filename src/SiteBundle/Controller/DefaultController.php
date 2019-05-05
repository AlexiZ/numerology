<?php

namespace SiteBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Services\Numerologie;
use ExtranetBundle\Form\NumerologieType;
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

    public function tryAction(Request $request, ManagerRegistry $registry)
    {
        $subject = new Analysis();
        $form = $this->createForm(NumerologieType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $subject->setData($this->numerologieService->exportData($subject));
            $subject->setUserId($this->getUser()->getId());
            $subject->setLevel(Analysis::LEVEL_FREE);

            $registry->getManager()->persist($subject);
            $registry->getManager()->flush();

            return $this->redirectToRoute('site_show_free', ['hash' => $subject->getHash()]);
        }

        return $this->render('SiteBundle:Default:try.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function showAction($hash, ManagerRegistry $registry)
    {
        /** @var Analysis $subject */
        $subject = $registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject) {
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
            $details['definition'] = $this->numerologieService->getDefinition($request->query->get('definition'));

            if ($request->query->has('value')) {
                try {
                    $details['value'] = $this->numerologieService->getAnalysis(
                        $request->query->get('value'),
                        $request->query->get('definition')
                    );
                } catch (\Exception $e) {}
            }
        }

        return new JsonResponse($details);
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

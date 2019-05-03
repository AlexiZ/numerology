<?php

namespace SiteBundle\Controller;

use ExtranetBundle\Entity\Numerologie;
use ExtranetBundle\Services\Numerologie as NumerologieService;
use ExtranetBundle\Form\NumerologieType;
use ExtranetBundle\Services\JsonIO;
use SiteBundle\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @var NumerologieService
     */
    private $numerologieService;

    public function __construct(NumerologieService $numerologieService)
    {
        $this->numerologieService = $numerologieService;
    }

    public function homepageAction()
    {
        return $this->render('SiteBundle:Default:index.html.twig');
    }

    public function tryAction(Request $request, JsonIO $jsonIO)
    {
        $subject = new Numerologie();
        $form = $this->createForm(NumerologieType::class, $subject);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $jsonIO->writeNumerology($subject, $this->numerologieService->exportData($subject));

            return $this->redirectToRoute('site_show', ['filename' => $filename]);
        }

        return $this->render('SiteBundle:Default:try.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function showAction(Request $request)
    {
        if (!$request->query->has('filename')) {
            return $this->redirectToRoute('site_try');
        }

        $numerologie = $this->numerologieService->getSubject($request->get('filename'));

        if (!$numerologie) {
            return $this->redirectToRoute('site_homepage');
        }

        return $this->render('@Site/Default/show.html.twig', [
            'subject' => $numerologie,
            'filename' => $request->get('filename')
        ]);
    }

    public function showDetailsAction(Request $request)
    {
        if (!$request->query->has('filename')) {
            return new JsonResponse(null, 404);
        }

        $numerologie = $this->numerologieService->getSubject($request->get('filename'));

        if (!$numerologie) {
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

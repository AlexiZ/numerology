<?php

namespace SiteBundle\Controller;

use ReCaptcha\ReCaptcha;
use SiteBundle\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function homepageAction()
    {
        return $this->render('SiteBundle:Default:index.html.twig');
    }

    public function contactAction(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recaptcha = new ReCaptcha($this->getParameter('google_recaptcha.secret_key'));
            $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

            if (!$resp->isSuccess()) {
                $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'envoi de votre message.');
            } else {
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
                    $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'envoi de votre message.');
                }
            }

            return $this->redirectToRoute('site_contact');
        }

        return $this->render('@Site/Default/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

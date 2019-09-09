<?php

namespace SiteBundle\Controller;

use ExtranetBundle\Entity\Analysis;
use ReCaptcha\ReCaptcha;
use SiteBundle\Form\ContactType;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public static $books = [
        1 => 'La Numérologie Essentielle',
        2 => 'Tarot de Marseille - L\'Interprète',
        3 => 'La Divination par le Tarot de Marseille',
        4 => 'L\'énergie du bien-être',
        5 => 'So Feng-Shui - La Chambre à coucher',
        6 => 'So Feng-Shui - La Cuisine',
        7 => 'So Feng-Shui - Le salon et la salle à manger',
        8 => 'So Feng-Shui - Cabinet des professions de santé et de bien-être',
        9 => 'So Feng-Shui - Chambre d\'enfant et d\'adolescent',
        10 => 'Feng Shui Business',
        11 => 'Vendez mieux votre maison grâce au Home Staging Feng Shui',
        12 => 'Le Pendule et la Balance',
    ];

    public function homepageAction()
    {
        $repository = $this->getDoctrine()->getRepository(Analysis::class);

        return $this->render('SiteBundle:Default:index.html.twig', [
            'examples' => $repository->getExamples(),
            'books' => self::$books,
        ]);
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
                    ->setFrom($this->getParameter('contact.from'))
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

    public function pageAction($slug, TwigEngine $twigEngine)
    {
        if (!$twigEngine->exists('@Site/Default/Page/' . $slug . '.html.twig')) {
            return $this->redirectToRoute('site_homepage');
        }

        return $this->render('@Site/Default/Page/' . $slug . '.html.twig');
    }
}

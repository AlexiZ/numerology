<?php

namespace SiteBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function paymentAction($hash, Request $request)
    {
        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || !$subject->isPaymentAvailable()) {
            return $this->redirectToRoute('site_homepage');
        }

        $request->getSession()->set('hash', $hash);

        return $this->render('SiteBundle:Default:payment.html.twig', [
            'subject' => $subject,
        ]);
    }

    public function paymentConfirmationAction(Request $request)
    {
        $hash = $request->getSession()->get('hash');

        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || !$subject->isPaymentAvailable()) {
            return $this->redirectToRoute('site_homepage');
        }
        $subject->setStatus(Analysis::STATUS_ACTIVE);
        $subject->setLevel(Analysis::LEVEL_PREMIUM);

        $this->registry->getManager()->persist($subject);
        $this->registry->getManager()->flush();

        $request->getSession()->remove('hash');

        return $this->redirectToRoute('site_show', ['hash' => $hash]);
    }

    public function paymentErrorAction(Request $request)
    {
        $hash = $request->getSession()->get('hash');

        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || !$subject->isPaymentAvailable()) {
            return $this->redirectToRoute('site_homepage');
        }

        $request->getSession()->remove('hash');
        $subject->setStatus(Analysis::STATUS_CANCELED);

        $this->registry->getManager()->persist($subject);
        $this->registry->getManager()->flush();

        return $this->render('SiteBundle:Default:payment_error.html.twig', [
            'subject' => $subject,
        ]);
    }

    public function paymentRollBackAction(Request $request)
    {
        $hash = $request->getSession()->get('hash');

        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || !$subject->isPaymentAvailable()) {
            return $this->redirectToRoute('site_homepage');
        }

        $request->getSession()->remove('hash');
        $subject->setLevel(Analysis::LEVEL_FREE);
        $subject->setStatus(Analysis::STATUS_ACTIVE);

        $this->registry->getManager()->persist($subject);
        $this->registry->getManager()->flush();

        return $this->redirectToRoute('site_show', ['hash' => $subject->getHash()]);
    }

    public function paymentDelayedAction(Request $request)
    {
        $hash = $request->getSession()->get('hash');

        /** @var Analysis $subject */
        $subject = $this->registry->getRepository(Analysis::class)->findOneByHash($hash);

        if (!$subject || !$subject->isPaymentAvailable()) {
            return $this->redirectToRoute('site_homepage');
        }

        $request->getSession()->remove('hash');
        $subject->setLevel(Analysis::LEVEL_FREE);
        $subject->setStatus(Analysis::STATUS_PENDING);

        $this->registry->getManager()->persist($subject);
        $this->registry->getManager()->flush();

        return $this->redirectToRoute('site_show', ['hash' => $subject->getHash()]);
    }
}

<?php

namespace ExtranetBundle\Controller\Admin;

use ExtranetBundle\Entity\Analysis;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository(Analysis::class);

        $history = $repository->getAllUsersHistory();

        return $this->render('@Extranet/Admin/index.html.twig', [
            'subjects' => $history,
        ]);
    }

    public function historyAction()
    {
        $repository = $this->getDoctrine()->getRepository(Analysis::class);

        $history = $repository->getAllUsersHistory();

        return new JsonResponse($history);
    }
}

<?php

namespace ExtranetBundle\Controller;

use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Services\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request, Utils $utils)
    {
        $repository = $this->getDoctrine()->getRepository(Analysis::class);

        if ($this->isGranted('ROLE_ANALYSIS_HISTORY')) {
            $history = $repository->getSingleUserHistory($this->getUser()->getId());
        } else {
            $history = $repository->getDemoHistory();
        }

        if ($request->query->has('ghost')) {
            switch ($request->query->get('ghost')) {
                case 'update-birthplacecoordinates':
                    $utils->updateBirthPlaceCoordinates();
                    break;
                default:
                    break;
            }
        }

        return $this->render('@Extranet/Analysis/index.html.twig', [
            'subjects' => $history,
        ]);
    }
}

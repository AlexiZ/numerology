<?php

namespace ExtranetBundle\Services;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;

class Utils
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }


    public function updateBirthPlaceCoordinates()
    {
        $repository = $this->registry->getRepository(Analysis::class);

        $all = $repository->findAll();
        foreach ($all as $one) {
            $data = $one->getData();
            $one->setBirthPlaceCoordinates($data['geolocation']);
            unset($data['geolocation']);
            $one->setData($data);

            $this->registry->getManager()->persist($one);
            $this->registry->getManager()->flush();
        }
    }
}
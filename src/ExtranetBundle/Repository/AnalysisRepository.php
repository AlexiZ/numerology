<?php

namespace ExtranetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ExtranetBundle\Entity\Analysis;

/**
 * AnalysisRepository
 */
class AnalysisRepository extends EntityRepository
{
    public function getSingleUserHistory($userId)
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('a.status = :status')
            ->setParameter('status', Analysis::STATUS_ACTIVE)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllUsersHistory()
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', Analysis::STATUS_ACTIVE)
            ->getQuery()
            ->getResult()
        ;
    }
}

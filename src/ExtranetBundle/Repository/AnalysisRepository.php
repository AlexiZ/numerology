<?php

namespace ExtranetBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use ExtranetBundle\Entity\Analysis;

/**
 * AnalysisRepository
 */
class AnalysisRepository extends EntityRepository
{
    public function getSingleUserHistory($userId, $asArray = false)
    {
        $returnFormat = AbstractQuery::HYDRATE_OBJECT;
        if ($asArray) {
            $returnFormat = AbstractQuery::HYDRATE_ARRAY;
        }

        return $this
            ->createQueryBuilder('a')
            ->where('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('a.status = :status')
            ->setParameter('status', Analysis::STATUS_ACTIVE)
            ->getQuery()
            ->getResult($returnFormat)
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

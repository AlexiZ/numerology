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
    public static $demoHashes = ['5d1270028f716', '5d1270ade2ac8', '5ccf253a33a63', '5d12675c06a39', '5d185ffd6e886', '5d185458c7184', '5d17d40c8b1a7', '5d17ccb52f9e0'];

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
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult($returnFormat)
        ;
    }

    public function getDemoHistory($asArray = false)
    {
        $returnFormat = AbstractQuery::HYDRATE_OBJECT;
        if ($asArray) {
            $returnFormat = AbstractQuery::HYDRATE_ARRAY;
        }

        return $this
            ->createQueryBuilder('a')
            ->where('a.hash IN (:hashes)')
            ->setParameter('hashes', self::$demoHashes)
            ->andWhere('a.status = :status')
            ->setParameter('status', Analysis::STATUS_ACTIVE)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult($returnFormat)
        ;
    }

    public function getAllUsersHistory($asArray = false)
    {
        $returnFormat = AbstractQuery::HYDRATE_OBJECT;
        if ($asArray) {
            $returnFormat = AbstractQuery::HYDRATE_ARRAY;
        }

        return $this
            ->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', Analysis::STATUS_ACTIVE)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult($returnFormat)
        ;
    }
}

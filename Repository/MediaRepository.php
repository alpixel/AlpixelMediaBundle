<?php

namespace Alpixel\Bundle\MediaBundle\Repository;

use Alpixel\Bundle\MediaBundle\Entity\Media;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class MediaRepository extends EntityRepository
{
    public function findExpiredMedias()
    {
        $qb = new QueryBuilder($this->getEntityManager());

        return $qb
        ->select('m')
        ->from('AlpixelMediaBundle:Media', 'm')
        ->where('m.lifetime is not null')
        ->andWhere('m.lifetime < :now')
        ->setParameter('now', new \DateTime())
        ->getQuery()
        ->getResult();
    }
}

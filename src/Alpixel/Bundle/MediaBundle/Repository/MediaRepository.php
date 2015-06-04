<?php
namespace Alpixel\Bundle\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Alpixel\Bundle\MediaBundle\Entity\Media;

class MediaRepository extends EntityRepository
{
    public function findExpiredMedias()
    {
        $qb = new QueryBuilder($this->getEntityManager());

        return $qb
        ->select('m')
        ->from('MediaBundle:Media', 'm')
        ->where('m.lifetime is not null')
        ->andWhere('m.lifetime < :now')
        ->setParameter('now', new \DateTime())
        ->getQuery()
        ->getResult();
    }
}

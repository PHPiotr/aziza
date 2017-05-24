<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GalleryRepository extends EntityRepository
{
    /**
     * @return int
     */
    public function getCountAll()
    {
        $qb = $this->createQueryBuilder('gallery');
        $qb->select('count(gallery.id)');
        $count = $qb->getQuery()->getSingleScalarResult();

        return (int)$count;
    }
}
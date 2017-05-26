<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SectionRepository extends EntityRepository
{
    /**
     * @return int
     */
    public function getCountAll()
    {
        $qb = $this->createQueryBuilder('section');
        $qb->select('count(section.id)');
        $count = $qb->getQuery()->getSingleScalarResult();

        return (int)$count;
    }
}
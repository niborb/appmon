<?php

namespace Rts\Bundle\AppMonBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AppRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AppRepository extends EntityRepository
{

    /**
     * @param int|\Rts\Bundle\AppMonBundle\Entity\Server $serverId [optional]
     * @return array
     */
    public function getByServerId($serverId = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a, s')
            ->from('RtsAppMonBundle:App', 'a')
            ->join('a.server', 's')
            ->orderBy('s.hostname', 'ASC');

        if (isset($serverId)) {
            if ($serverId instanceof Server) {
                $serverId = $serverId->getId();
            }
            $qb->where('s.id = :serverId')
                ->setParameter('serverId', $serverId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int|\Rts\Bundle\AppMonBundle\Entity\Category $categoryId [optional]
     * @return array
     */
    public function getByCategoryId($categoryId = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a, c, s')
            ->from('RtsAppMonBundle:App', 'a')
            ->join('a.category', 'c')
            ->join('a.server', 's')
            ->orderBy('s.hostname', 'ASC');

        if (isset($categoryId)) {
            if ($categoryId instanceof AppCategory) {
                $categoryId = $categoryId->getId();
            }
            $qb->where('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }

    public function getBySearchArguments(
        $serverId = null,
        $categoryId = null,
        $search = null
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a, c, s')
            ->from('RtsAppMonBundle:App', 'a')
            ->join('a.server', 's')
            ->leftJoin('a.category', 'c')
            ->orderBy('s.hostname', 'ASC')
        ;

        if (!empty($search)) {
            $qb->where($qb->expr()->like('s.hostname', ':search'))
                ->orWhere($qb->expr()->like('s.ip_address', ':search'))
                ->orWhere($qb->expr()->like('a.name', ':search'))
                ->orWhere($qb->expr()->like('a.meta_data_json', ':search'))
                ->orWhere($qb->expr()->like('c.name', ':search'))
                ->setParameter('search', "%$search%")
            ;
        }

        if (!empty($serverId)) {
            $qb->andWhere('s.id = :serverId')
                ->setParameter('serverId', $serverId)
            ;
        }

        if (!empty($categoryId)) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId)
            ;
        }

        return $qb->getQuery()->getResult();
    }

}

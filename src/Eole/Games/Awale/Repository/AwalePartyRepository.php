<?php

namespace Eole\Games\Awale\Repository;

use Doctrine\ORM\EntityRepository;
use Eole\Games\Awale\Model\AwaleParty;

class AwalePartyRepository extends EntityRepository
{
    /**
     * @param int $id
     *
     * @return AwaleParty
     */
    public function findFullById($id)
    {
        $query = $this->createQueryBuilder('ap')
            ->addSelect('p, h, g, s, pl')
            ->leftJoin('ap.party', 'p')
            ->leftJoin('p.host', 'h')
            ->leftJoin('p.game', 'g')
            ->leftJoin('p.slots', 's')
            ->leftJoin('s.player', 'pl')
            ->where('p.id = :id')
            ->andWhere('g.name = :gameName')
            ->setParameter('id', $id)
            ->setParameter('gameName', 'awale')
        ;

        return $query
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

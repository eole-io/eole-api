<?php

namespace Eole\Core\Repository;

use Doctrine\ORM\EntityRepository;
use Eole\Core\Model\Party;
use Eole\Core\Model\Slot;

class PartyRepository extends EntityRepository
{
    /**
     * @param int $id
     * @param string $gameName set game name to ensure party is from this game.
     *
     * @return Party
     */
    public function findFullPartyById($id, $gameName = null)
    {
        $query = $this->createQueryBuilder('p')
            ->addSelect('g, s, pl')
            ->leftJoin('p.game', 'g')
            ->leftJoin('p.slots', 's')
            ->leftJoin('s.player', 'pl')
            ->where('p.id = :id')
            ->setParameter('id', $id)
        ;

        if (null !== $gameName) {
            $query
                ->andWhere('g.name = :gameName')
                ->setParameter('gameName', $gameName)
            ;
        }

        return $query
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param Party $party
     */
    public function updateState(Party $party)
    {
        $this->_em->createQueryBuilder()
            ->update('Eole:Party', 'p')
            ->set('p.state', $party->getState())
            ->where('p.id = :id')
            ->setParameter('id', $party->getId())
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @param Slot $slot
     */
    public function updateScore(Slot $slot)
    {
        $this->_em->createQueryBuilder()
            ->update('Eole:Slot', 's')
            ->set('s.score', $slot->getScore())
            ->where('s.id = :id')
            ->setParameter('id', $slot->getId())
            ->getQuery()
            ->execute()
        ;
    }
}

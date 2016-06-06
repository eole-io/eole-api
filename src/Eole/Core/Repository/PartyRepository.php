<?php

namespace Eole\Core\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Eole\Core\Model\Game;
use Eole\Core\Model\Party;
use Eole\Core\Model\Slot;

class PartyRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function findFullParties()
    {
        return $this->createQueryBuilder('party')
            ->addSelect('host')
            ->addSelect('slot', 'player')
            ->leftJoin('party.host', 'host')
            ->leftJoin('party.slots', 'slot')
            ->leftJoin('slot.player', 'player')
            ->addOrderBy('party.id')
            ->addOrderBy('slot.order')
        ;
    }

    /**
     * @return Party[]
     */
    public function findAll()
    {
        return $this->findFullParties()
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Game $game
     *
     * @return Party[]
     */
    public function findAllByGame(Game $game)
    {
        $query = $this->findFullParties()
            ->where('party.game = :game')
            ->setParameter('game', $game)
        ;

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $id
     * @param string $gameName set game name to ensure party is from this game.
     *
     * @return Party
     */
    public function findFullPartyById($id, $gameName = null)
    {
        $query = $this->findFullParties()
            ->addSelect('game')
            ->leftJoin('party.game', 'game')
            ->where('party.id = :id')
            ->setParameter('id', $id)
        ;

        if (null !== $gameName) {
            $query
                ->andWhere('game.name = :gameName')
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

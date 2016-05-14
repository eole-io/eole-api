<?php

namespace Eole\Core\Repository;

use Doctrine\ORM\EntityRepository;
use Eole\Core\Model\Game;
use Eole\Core\Model\Party;
use Eole\Core\Model\Slot;

class PartyRepository extends EntityRepository
{
    /**
     * @param Game $game
     *
     * @return Party[]
     */
    public function findAllByGame(Game $game)
    {
        $query = $this->createQueryBuilder('party')
            ->addSelect('host')
            ->leftJoin('party.host', 'host')
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
        $query = $this->createQueryBuilder('party')
            ->addSelect('game, slot, player, host')
            ->leftJoin('party.game', 'game')
            ->leftJoin('party.host', 'host')
            ->leftJoin('party.slots', 'slot')
            ->leftJoin('slot.player', 'player')
            ->where('party.id = :id')
            ->addOrderBy('slot.order')
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

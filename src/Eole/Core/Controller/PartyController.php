<?php

namespace Eole\Core\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Eole\Core\ApiResponse;
use Eole\Core\Model\Game;
use Eole\Core\Model\Player;
use Eole\Core\Model\Party;
use Eole\Core\Repository\PartyRepository;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Event\SlotEvent;
use Eole\Core\Service\PartyManager;

class PartyController
{
    use LoggedPlayerTrait;

    /**
     * @var PartyRepository
     */
    private $partyRepository;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @param PartyRepository $partyRepository
     * @param ObjectManager $om
     * @param PartyManager $partyManager
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        PartyRepository $partyRepository,
        ObjectManager $om,
        PartyManager $partyManager,
        EventDispatcher $dispatcher
    ) {
        $this->partyRepository = $partyRepository;
        $this->om = $om;
        $this->partyManager = $partyManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return ApiResponse
     */
    public function getParties()
    {
        $parties = $this->partyRepository->findAll();

        return new ApiResponse($parties);
    }

    /**
     * @param Game $game
     *
     * @return ApiResponse
     */
    public function getPartiesForGame(Game $game)
    {
        $parties = $this->partyRepository->findBy(array(
            'game' => $game,
        ));

        return new ApiResponse($parties);
    }

    /**
     * @param Game $game
     *
     * @return ApiResponse
     *
     * @throws HttpException when no logged player.
     */
    public function createParty(Game $game)
    {
        $this->mustBeLogged();

        $party = $this->partyManager->createParty($game, $this->loggedPlayer);

        $this->dispatcher->dispatch(PartyEvent::CREATE_BEFORE, new PartyEvent($party));

        $this->om->persist($party);
        $this->om->flush();

        $this->dispatcher->dispatch(PartyEvent::CREATE_AFTER, new PartyEvent($party));

        return new ApiResponse($party, Response::HTTP_CREATED);
    }

    /**
     * @param Party $party
     *
     * @return ApiResponse
     */
    public function getParty(Party $party)
    {
        return new ApiResponse($party);
    }

    /**
     * A player joins a party.
     *
     * @param Party $party
     * @param Player $player
     *
     * @return ApiResponse with player position
     *
     * @throws HttpException when no logged player.
     * @throws ConflictHttpException when cannot join party.
     */
    public function joinParty(Party $party)
    {
        $this->mustBeLogged();

        if ($this->partyManager->hasPlayer($party, $this->loggedPlayer)) {
            throw new ConflictHttpException('Player already in party.');
        }

        $this->dispatcher->dispatch(SlotEvent::JOIN_BEFORE, new SlotEvent($party, $this->loggedPlayer));

        try {
            $position = $this->partyManager->addPlayer($party, $this->loggedPlayer);
        } catch (\OverflowException $e) {
            throw new ConflictHttpException('Party is full, cannot join.', $e);
        }

        $this->om->persist($party);
        $this->om->flush();

        $joinedSlot = $party->getSlot($position);
        $this->dispatcher->dispatch(SlotEvent::JOIN_AFTER, SlotEvent::createFromSlot($joinedSlot));

        return new ApiResponse($position);
    }
}

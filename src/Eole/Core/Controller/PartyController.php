<?php

namespace Eole\Core\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Eole\Core\Model\Game;
use Eole\Core\Model\Player;
use Eole\Core\Repository\PartyRepository;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Service\PartyManager;

class PartyController
{
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
     * Authenticated player
     *
     * @var Player|null
     */
    private $loggedPlayer;

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
     * @param Player $player
     *
     * @return self
     */
    public function setLoggedPlayer(Player $player)
    {
        $this->loggedPlayer = $player;

        return $this;
    }

    /**
     * @return JsonResponse
     */
    public function getParties()
    {
        $parties = $this->partyRepository->findAll();

        return new JsonResponse($parties);
    }

    /**
     * @param Game $game
     *
     * @return JsonResponse
     */
    public function getPartiesForGame(Game $game)
    {
        $parties = $this->partyRepository->findBy(array(
            'game' => $game,
        ));

        return new JsonResponse($parties);
    }

    /**
     * @param Game $game
     *
     * @return JsonResponse
     */
    public function createParty(Game $game)
    {
        $party = $this->partyManager->createParty($game, $this->loggedPlayer);

        $this->dispatcher->dispatch(PartyEvent::CREATE_BEFORE, new PartyEvent($party));

        $this->om->persist($party);
        $this->om->flush();

        $this->dispatcher->dispatch(PartyEvent::CREATE_AFTER, new PartyEvent($party));

        return new JsonResponse($party);
    }
}

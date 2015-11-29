<?php

namespace Eole\Core\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Eole\Core\Model\Game;
use Eole\Core\Model\Party;
use Eole\Core\Model\Player;
use Eole\Core\Repository\PartyRepository;
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
     * Authenticated player
     *
     * @var Player|null
     */
    private $loggedPlayer;

    /**
     * @param PartyRepository $partyRepository
     * @param ObjectManager $om
     * @param PartyManager $partyManager
     */
    public function __construct(PartyRepository $partyRepository, ObjectManager $om, PartyManager $partyManager)
    {
        $this->partyRepository = $partyRepository;
        $this->om = $om;
        $this->partyManager = $partyManager;
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

        $this->om->persist($party);
        $this->om->flush();

        return new JsonResponse($party);
    }
}

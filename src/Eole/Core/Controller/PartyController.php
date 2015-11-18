<?php

namespace Eole\Core\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alcalyn\UserApi\Api\ApiInterface;
use Eole\Core\Model\Game;
use Eole\Core\Model\Party;
use Eole\Core\Repository\GameRepository;
use Eole\Core\Repository\PartyRepository;

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
     * @param PartyRepository $partyRepository
     * @param ObjectManager $om
     */
    public function __construct(PartyRepository $partyRepository, ObjectManager $om)
    {
        $this->partyRepository = $partyRepository;
        $this->om = $om;
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
        $party = new Party();

        $party->setGame($game);

        $this->om->persist($party);
        $this->om->flush();

        return new JsonResponse($party);
    }
}

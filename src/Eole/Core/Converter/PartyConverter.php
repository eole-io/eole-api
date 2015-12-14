<?php

namespace Eole\Core\Converter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Eole\Core\Model\Party;
use Eole\Core\Repository\PartyRepository;

class PartyConverter
{
    /**
     * @var PartyRepository
     */
    private $partyRepository;

    /**
     * @param PartyRepository $partyRepository
     */
    public function __construct(PartyRepository $partyRepository)
    {
        $this->partyRepository = $partyRepository;
    }

    /**
     * @param int|null $partyId
     * @param Request $request
     *
     * @return Party|null
     *
     * @throws NotFoundHttpException
     */
    public function convert($partyId, Request $request)
    {
        if (null === $partyId) {
            return null;
        }

        $gameName = $request->attributes->get('game')->getName();

        if (null === $gameName) {
            throw new BadRequestHttpException("Game invalid.");
        }

        if (is_numeric($partyId)) {
            $party = $this->partyRepository->findFullPartyById($partyId, $gameName);
        } else {
            throw new BadRequestHttpException('Party id must be numeric');
        }

        if (null === $party) {
            throw new NotFoundHttpException("Party '$partyId' not found for game '$gameName'.");
        }

        return $party;
    }
}

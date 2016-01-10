<?php

namespace Eole\Games\Awale;

use Alcalyn\Awale\Exception\AwaleException;
use Alcalyn\Awale\Awale;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpFoundation\Request;
use Eole\Core\ApiResponse;
use Eole\Core\Service\PartyManager;
use Eole\Core\Controller\LoggedPlayerTrait;
use Eole\Games\Awale\Repository\AwalePartyRepository;

class Controller
{
    use LoggedPlayerTrait;

    /**
     * @var AwalePartyRepository
     */
    private $awalePartyRepository;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @param AwalePartyRepository $awalePartyRepository
     * @param ObjectManager $om
     * @param PartyManager $partyManager
     */
    public function __construct(
        AwalePartyRepository $awalePartyRepository,
        ObjectManager $om,
        PartyManager $partyManager
    ) {
        $this->awalePartyRepository = $awalePartyRepository;
        $this->om = $om;
        $this->partyManager = $partyManager;
    }

    /**
     * @return ApiResponse
     */
    public function getTest()
    {
        return new ApiResponse(array(
            'test' => 'ok',
        ));
    }

    /**
     * @param int $id
     *
     * @return ApiResponse
     *
     * @throws NotFoundHttpException
     */
    public function findById($id)
    {
        $awaleParty = $this->awalePartyRepository->findFullById($id);

        if (null === $awaleParty) {
            throw new NotFoundHttpException('Party of game awale with id "'.$id.'" does not exists.');
        }

        return new ApiResponse($awaleParty);
    }

    /**
     * @param Request $request
     *
     * @return ApiResponse
     *
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException if party not found.
     * @throws AccessDeniedHttpException if observer tries to play.
     * @throws ConflictHttpException if not player's turn to play.
     */
    public function play(Request $request)
    {
        $this->mustBeLogged();

        if (!$request->request->has('move')) {
            throw new BadRequestHttpException('Missing "move" argument.');
        }

        if (!$request->request->has('party_id')) {
            throw new BadRequestHttpException('Missing "party_id" argument.');
        }

        $move = $request->request->getInt('move');
        $partyId = $request->request->getInt('party_id');

        if ($move < 0 || $move > 5) {
            throw new BadRequestHttpException('Argument "move" must be between 0 and 5.');
        }

        $awaleParty = $this->awalePartyRepository->findFullById($partyId);

        if (null === $awaleParty) {
            throw new NotFoundHttpException('AwaleParty with id "'.$partyId.'" not exists.');
        }

        $party = $awaleParty->getParty();
        $partyHasPlayer = $this->partyManager->hasPlayer($party, $this->loggedPlayer);

        if (!$partyHasPlayer) {
            throw new AccessDeniedHttpException('Observers cannot play.');
        }

        $players = array(
            0 => Awale::PLAYER_0,
            1 => Awale::PLAYER_1,
        );
        $slotPosition = $this->partyManager->getPlayerPosition($party, $this->loggedPlayer);
        $player = $players[$slotPosition];

        if (!$awaleParty->isPlayerTurn($player)) {
            throw new ConflictHttpException('Not your turn to play.');
        }

        try {
            $awaleParty->play($player, $move);
        } catch (AwaleException $e) {
            throw new BadRequestHttpException('Invalid move.', $e);
        }

        $this->om->persist($awaleParty);
        $this->om->flush();

        return new ApiResponse(array(
            'move' => $move,
            'player' => $this->loggedPlayer->getUsername(),
            'grid_updated' => $awaleParty->getGrid(),
        ));
    }
}

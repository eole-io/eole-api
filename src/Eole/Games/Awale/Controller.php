<?php

namespace Eole\Games\Awale;

use Alcalyn\Awale\Exception\AwaleException;
use Alcalyn\Awale\Awale;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpFoundation\Request;
use Eole\Core\ApiResponse;
use Eole\Core\Model\Party;
use Eole\Core\Service\PartyManager;
use Eole\Core\Controller\LoggedPlayerTrait;
use Eole\Games\Awale\Event\AwaleEvent;
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param AwalePartyRepository $awalePartyRepository
     * @param ObjectManager $om
     * @param PartyManager $partyManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        AwalePartyRepository $awalePartyRepository,
        ObjectManager $om,
        PartyManager $partyManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->awalePartyRepository = $awalePartyRepository;
        $this->om = $om;
        $this->partyManager = $partyManager;
        $this->dispatcher = $dispatcher;
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

        if (Party::ACTIVE !== $party->getState()) {
            throw new AccessDeniedHttpException('Party is not active.');
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

        $party->getSlot(0)->setScore($awaleParty->getGrid()[0]['attic']);
        $party->getSlot(1)->setScore($awaleParty->getGrid()[1]['attic']);

        $this->dispatcher->dispatch(AwaleEvent::PLAY, new AwaleEvent($awaleParty));

        $winner = $awaleParty->getWinner();
        $partyEnded = null !== $winner;

        if ($partyEnded) {
            $this->partyManager->endParty($party);
            $this->dispatcher->dispatch(AwaleEvent::PARTY_END, new AwaleEvent($awaleParty, $winner));
        }

        $this->om->persist($awaleParty);
        $this->om->flush();

        return new ApiResponse(array(
            'valid' => true,
            'current_player' => $awaleParty->getCurrentPlayer(),
            'grid' => $awaleParty->getGrid(),
            'winner' => $winner,
        ));
    }
}

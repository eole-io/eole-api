<?php

namespace Eole\Games\TicTacToe;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Alcalyn\TicTacToe\Exception\TicTacToeException;
use Alcalyn\TicTacToe\TicTacToe;
use Ratchet\Wamp\WampConnection;
use Eole\Core\Model\Party;
use Eole\Core\Event\SlotEvent;
use Eole\Core\Service\PartyManager;
use Eole\Core\Repository\PartyRepository;
use Eole\WebSocket\Topic as BaseTopic;

class Topic extends BaseTopic implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    const INFINITE_MODE_ENABLED = true;

    /**
     * @var Party
     */
    private $party;

    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @var PartyRepository
     */
    private $partyRepository;

    /**
     * @var TicTacToe
     */
    private $tictactoe;

    /**
     * @param string $topicPath
     * @param Party $party
     * @param PartyManager $partyManager
     */
    public function __construct($topicPath, Party $party, PartyManager $partyManager, PartyRepository $partyRepository)
    {
        parent::__construct($topicPath);

        $this->party = $party;
        $this->partyManager = $partyManager;
        $this->partyRepository = $partyRepository;

        $this->tictactoe = new TicTacToe();

        $randomSymbol = [TicTacToe::X, TicTacToe::O][mt_rand() % 2];

        $this->tictactoe->setCurrentPlayer($randomSymbol);
    }

    /**
     * @param SlotEvent $event
     */
    public function onPlayerJoin(SlotEvent $event)
    {
        if ($event->getParty()->getId() !== $this->party->getId()) {
            return;
        }

        $this->party = $event->getParty();

        $this->broadcast(array(
            'type' => 'join',
            'player' => $event->getPlayer(),
            'position' => $this->partyManager->getPlayerPosition($event->getParty(), $event->getPlayer()),
        ));

        $this->partyManager->startParty($this->party);
        $this->partyRepository->updateState($this->party);
    }

    /**
     * {@InheritDoc}
     */
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
        switch ($event['type']) {
            case 'move':
                $this->onMove($conn, $topic, $event);
                break;

            case 'need-refresh':
                $this->sendAll($conn, $topic);
                break;

            default:
                echo 'Oops, strange event type: "'.$event['type'].'"'.PHP_EOL;
        }
    }

    /**
     * Perform a move a broadcast all clients.
     *
     * @param WampConnection $conn
     * @param string $topic
     * @param array $event
     */
    private function onMove(WampConnection $conn, $topic, $event)
    {
        $player = $conn->player;
        $playerPosition = $this->partyManager->getPlayerPosition($this->party, $player);

        if (null === $playerPosition) {
            echo 'Argh! Observer trying to play a move!'.PHP_EOL;
            return;
        }

        $col = (int) $event['col'];
        $row = (int) $event['row'];
        $symbol = [TicTacToe::X, TicTacToe::O][$playerPosition];

        try {
            $this->tictactoe->play($col, $row, $symbol);
        } catch (TicTacToeException $e) {
            echo 'Argh! Invalid move: '.$e->getMessage().PHP_EOL;
            return;
        }

        $this->broadcast(array(
            'type' => 'move',
            'move' => array(
                'col' => $col,
                'row' => $row,
                'symbol' => $symbol,
            ),
            'current_player' => $this->tictactoe->getCurrentPlayer(),
        ));

        if (null !== $winner = $this->tictactoe->getWinner()) {
            if (TicTacToe::NONE !== $winner) {
                $slot = $this->party->getSlot((TicTacToe::X === $winner) ? 0 : 1);
                $slot->incrementScore();
                $this->partyRepository->updateScore($slot);
            }

            $this->broadcast(array(
                'type' => 'end',
                'winner' => $winner,
                'brochette' => $this->tictactoe->getBrochette(),
            ));

            if (self::INFINITE_MODE_ENABLED) {
                $this->tictactoe->clearGrid();
                $this->sendRestartParty();
            } else {
                $this->partyManager->endParty($this->party);
                $this->partyRepository->updateState($this->party);
            }
        }
    }

    /**
     * Send all data about current party to one client.
     *
     * @param WampConnection $conn
     * @param string $topic
     */
    private function sendAll(WampConnection $conn, $topic)
    {
        $conn->event($topic, array(
            'type' => 'init',
            'tictactoe' => $this->normalizer->normalize($this->tictactoe),
            'party' => $this->normalizer->normalize($this->party),
        ));
    }

    /**
     * Send restart notification to all subscribers.
     */
    private function sendRestartParty()
    {
        $this->broadcast(array(
            'type' => 'restart',
            'tictactoe' => $this->tictactoe,
            'party' => $this->party,
        ));
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SlotEvent::JOIN_AFTER => array(
                array('onPlayerJoin'),
            ),
        );
    }
}

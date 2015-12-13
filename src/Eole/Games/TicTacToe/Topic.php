<?php

namespace Eole\Games\TicTacToe;

use Alcalyn\TicTacToe\Exception\TicTacToeException;
use Alcalyn\TicTacToe\TicTacToe;
use Ratchet\Wamp\WampConnection;
use Eole\Core\Model\Party;
use Eole\Core\Service\PartyManager;
use Eole\WebSocket\Topic as BaseTopic;

class Topic extends BaseTopic
{
    /**
     * @var Party
     */
    private $party;

    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @var TicTacToe
     */
    private $tictactoe;

    /**
     * @param string $topicPath
     * @param Party $party
     */
    public function __construct($topicPath, Party $party, PartyManager $partyManager)
    {
        parent::__construct($topicPath);

        $this->party = $party;
        $this->partyManager = $partyManager;
        $this->tictactoe = new TicTacToe();

        $randomSymbol = [TicTacToe::X, TicTacToe::O][mt_rand() % 2];

        $this->tictactoe->setCurrentPlayer($randomSymbol);
    }

    /**
     * {@InheritDoc}
     */
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        $player = $conn->player;
        $party = $this->party;

        if ($this->partyManager->hasFreeSlot($party) && !$this->partyManager->hasPlayer($party, $player)) {
            $this->partyManager->addPlayer($party, $player);
        }

        $conn->event($topic, array(
            'type' => 'init',
            'tictactoe' => $this->normalizer->normalize($this->tictactoe),
            'party' => $this->normalizer->normalize($this->party),
        ));
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(WampConnection $conn, $topic, $event)
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

            $this->broadcast(array(
                'type' => 'move',
                'move' => array(
                    'col' => $col,
                    'row' => $row,
                    'symbol' => $symbol,
                ),
            ));
        } catch (TicTacToeException $e) {
            echo 'Argh! Invalid move: '.$e->getMessage().PHP_EOL;
        }
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);
    }
}

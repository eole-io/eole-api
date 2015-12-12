<?php

namespace Eole\Games\TicTacToe;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Eole\WebSocket\Routing\TopicRoute;
use Eole\Games\TicTacToe\Topic;

class TicTacToeProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $partyRepository = $app['orm.em']->getRepository('Eole:Party');

        $tictactoePartiesFactory = function ($topicPath, array $arguments) use ($partyRepository) {
            $partyId = $arguments['party_id'];
            $party = $partyRepository->findFullPartyById($partyId, 'tictactoe');

            return new Topic($topicPath, $party);
        };

        $app['eole.games.tictactoe.topic.factory'] = $app->protect($tictactoePartiesFactory);

        $app['eole.websocket.routes']->add('eole_games_tictactoe_party', new TopicRoute(
            'eole/games/tictactoe/parties/{party_id}',
            'eole.games.tictactoe.topic.factory',
            array(),
            array('party_id' => '^[0-9]+$')
        ));
    }
}

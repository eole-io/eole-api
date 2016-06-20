<?php

namespace Eole\Games\TicTacToe;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TicTacToeWebsocketProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app
            ->topic('eole/games/tictactoe/parties/{party_id}', function ($topicPattern, $arguments) use ($app) {
                $partyId = $arguments['party_id'];
                $partyRepository = $app['orm.em']->getRepository('Eole:Party');
                $party = $partyRepository->findFullPartyById($partyId, 'tictactoe');
                $partyManager = $app['eole.party_manager'];

                return new Websocket\Topic($topicPattern, $party, $partyManager, $partyRepository);
            })
            ->assert('party_id', '^[0-9]+$')
        ;
    }
}

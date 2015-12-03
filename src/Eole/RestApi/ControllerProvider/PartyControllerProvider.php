<?php

namespace Eole\RestApi\ControllerProvider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class PartyControllerProvider implements ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $partyController = 'eole.controller.party';

        $controllers->get('/parties', $partyController.':getParties');
        $controllers->get('/games/{game}/parties', $partyController.':getPartiesForGame');
        $controllers->post('/games/{game}/parties', $partyController.':createParty');

        $controllers->convert('game', 'eole.converter.game:convert');

        return $controllers;
    }
}

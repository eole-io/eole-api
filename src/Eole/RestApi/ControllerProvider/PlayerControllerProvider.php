<?php

namespace Eole\RestApi\ControllerProvider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class PlayerControllerProvider implements ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $playerController = 'eole.controller.player';

        $controllers->get('/players', $playerController.':getUsers');
        $controllers->get('/players/{username}', $playerController.':getUser');
        $controllers->post('/players', $playerController.':postUser');
        $controllers->post('/players/guest', $playerController.':postGuest');
        $controllers->post('/players/change-password', $playerController.':changePassword');
        $controllers->post('/players/register', $playerController.':registerGuest');
        $controllers->delete('/players/{username}', $playerController.':deleteUser');
        $controllers->get('/players-count', $playerController.':countUsers');
        $controllers->get('/auth/me', $playerController.':authMe');

        return $controllers;
    }
}

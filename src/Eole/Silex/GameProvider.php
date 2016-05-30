<?php

namespace Eole\Silex;

use Doctrine\Common\Persistence\ObjectManager;
use Eole\Silex\Application as SilexApplication;
use Eole\Core\Model\Game;

abstract class GameProvider extends Mod
{
    /**
     * @return Game instance of game.
     */
    abstract public function createGame();

    /**
     * Persist game fixtures.
     *
     * @param SilexApplication $app
     * @param ObjectManager $om
     */
    public function createFixtures(SilexApplication $app, ObjectManager $om)
    {
        // noop
    }
}

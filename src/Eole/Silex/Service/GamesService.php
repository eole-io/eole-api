<?php

namespace Eole\Silex\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Eole\Silex\GameInterface;

class GamesService
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param GameInterface $gameInterface
     */
    public function installGame(GameInterface $gameInterface)
    {
        $this->om->persist($gameInterface->createGame());
    }
}

<?php

namespace Eole\Core\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Eole\Core\Model\Player;

trait LoggedPlayerTrait
{
    /**
     * Authenticated player
     *
     * @var Player|null
     */
    protected $loggedPlayer;

    /**
     * @param Player $player
     *
     * @return self
     */
    public function setLoggedPlayer(Player $player)
    {
        $this->loggedPlayer = $player;

        return $this;
    }

    /**
     * Throw unauthorized http exception if no player.
     *
     * @throws HttpException
     */
    protected function mustBeLogged()
    {
        if (null === $this->loggedPlayer) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'You must be logged to do this action.');
        }
    }
}

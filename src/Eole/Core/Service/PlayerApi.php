<?php

namespace Eole\Core\Service;

use Alcalyn\DoctrineUserApi\Api\DoctrineApi;
use Eole\Core\Model\Player;

class PlayerApi extends DoctrineApi
{
    /**
     * Create an unique random guest pseudo.
     *
     * @return string
     */
    public function generateGuestPseudo()
    {
        do {
            $username = 'Guest '.rand(10000, 99999);
        } while (null !== $this->getUser($username));

        return $username;
    }

    /**
     * Generate a guest.
     *
     * @param string $password
     *
     * @return Player
     */
    public function createGuest($password = null)
    {
        $username = $this->generateGuestPseudo();

        if (null === $password) {
            $password = md5(mt_rand());
        }

        $guest = $this->userManager->createUser($username, $password);
        $guest->setGuest(true);

        $this->userRepository->saveUser($guest);

        return $guest;
    }

    /**
     * @param Player|string $guest username or Player instance
     * @param string $username
     * @param string $password
     *
     * @return Player
     *
     * @throws AlreadyAPlayerException
     */
    public function registerGuest($guest, $username, $password)
    {
        if (is_string($guest)) {
            $guest = $this->getUser($guest);
        }

        $this->userManager->setGuestAsPlayer($guest, $username, $password);

        $this->userRepository->updateUser($guest);

        return $guest;
    }
}

<?php

namespace Eole\Core\Service;

use Alcalyn\UserApi\Service\UserManager;
use Eole\Core\Exception\AlreadyAPlayerException;
use Eole\Core\Model\Player;

class PlayerManager extends UserManager
{
    /**
     * Upgrade a guest player to a registered player.
     *
     * @param Player $guest
     * @param string $username
     * @param string $password
     *
     * @return Player
     *
     * @throws AlreadyAPlayerException
     */
    public function setGuestAsPlayer(Player $guest, $username, $password)
    {
        if (!$guest->isGuest()) {
            throw new AlreadyAPlayerException('Cannot set guest as player, provided guest is already a player.');
        }

        $player = $guest
            ->setGuest(false)
            ->setUsername($username)
        ;

        $this->updatePassword($player, $password);

        return $player;
    }

    /**
     * @param Player $player
     *
     * @return string
     */
    public function generateWsseToken(Player $player)
    {
        $nonce = base64_encode(substr(sha1(mt_rand()), -16));
        $created = (new \DateTime())->format(\DateTime::ATOM);
        $secret = $player->getPassword();
        $digest = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));

        return sprintf(
            'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $player->getUsername(),
            $digest,
            $nonce,
            $created
        );
    }

    /**
     * {@InheritDoc}
     */
    public function generateSalt()
    {
        $salts = array(
            'Sel de Guerande',
            'Sel de mer',
            'Poivre du Sichuan',
            'Poivre rose',
            'Poivre de Cayenne',
            'Poivre des moines',
            'Poivre de la Jamaique',
            'Poivre de Selim',
            'Poivre de Guinee',
            'Poivre de Tasmanie',
            'Poivre noir du Sarawak',
            'Poivre de Kampot',
            'Poivre de Malabar',
            'Poivre blanc de Penja',
            'Poivre de chez Auchan',
        );

        $salt = $salts[array_rand($salts)];

        return substr($salt.'-'.parent::generateSalt(), 0, 46);
    }
}

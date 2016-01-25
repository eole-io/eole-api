<?php

namespace Eole\OAuth2\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Alcalyn\Wsse\Security\Exception\WsseAuthenticationException;
use Alcalyn\Wsse\Security\Authentication\Token\WsseUserToken;

class PasswordDigestValidator implements WsseTokenValidatorInterface
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@InheritDoc}
     *
     * @throws NonceExpiredException
     */
    public function validateDigest(WsseUserToken $wsseToken, UserInterface $user)
    {
        $created = $wsseToken->created;
        $nonce = $wsseToken->nonce;
        $digest = $wsseToken->digest;
        $secret = $user->getPassword();

        // Check created time is not too far in the future (leaves 5 minutes margin)
        if (strtotime($created) > (time() + 300)) {
            throw new WsseAuthenticationException(sprintf(
                'Token created date cannot be in future (%d seconds in the future).',
                time() - strtotime($created)
            ));
        }

        // Expire timestamp after 5 minutes
        if (strtotime($created) < (time() - 300)) {
            throw new WsseAuthenticationException(sprintf(
                'Token created date has expired its 300 seconds of validity (%d seconds).',
                strtotime($created) - time()
            ));
        }

        // Validate that the nonce is *not* used in the last 10 minutes
        // if it has, this could be a replay attack
        if (file_exists($this->cacheDir.'/'.$nonce) && file_get_contents($this->cacheDir.'/'.$nonce) + 600 > time()) {
            throw new NonceExpiredException('Previously used nonce detected.');
        }
        // If cache directory does not exist we create it
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        file_put_contents($this->cacheDir.'/'.$nonce, time());

        // Validate Secret
        $expected = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));

        if (!StringUtils::equals($expected, $digest)) {
            throw new WsseAuthenticationException('Token digest is not valid.');
        }

        return true;
    }
}

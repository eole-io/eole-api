<?php

namespace Alcalyn\UserApi\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Alcalyn\UserApi\Model\User;

class UserManager
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @param EncoderFactoryInterface $encoderFactory
     * @param string $userClass
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, $userClass = 'Alcalyn\UserApi\Model\User')
    {
        $this->encoderFactory = $encoderFactory;
        $this->userClass = $userClass;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return User
     */
    public function createUser($username, $password)
    {
        $user = new $this->userClass();

        $this->updatePassword($user, $password);

        $user
            ->setUsername($username)
            ->setEnabled(true)
        ;

        return $user;
    }

    /**
     * Generate password hash and salt and set it to $user.
     *
     * @param User $user
     * @param string $password
     *
     * @return User
     */
    public function updatePassword(User $user, $password)
    {
        $encoded = $this->encodePassword($password, $user);

        return $user
            ->setPasswordHash($encoded['hash'])
            ->setPasswordSalt($encoded['salt'])
        ;
    }

    /**
     * Encode a password with a generated salt.
     *
     * @param string $password
     * @param UserInterface $user instance of user, default to user class.
     * @param string $passwordSalt generate one if not defined.
     *
     * @return array with hash and salt.
     */
    public function encodePassword($password, UserInterface $user = null, $passwordSalt = null)
    {
        if (null === $user) {
            $user = new $this->userClass();
        }

        if (null === $passwordSalt) {
            $passwordSalt = $this->generateSalt();
        }

        $encoder = $this->encoderFactory->getEncoder($user);
        $passwordHash = $encoder->encodePassword($password, $passwordSalt);

        return array(
            'hash' => $passwordHash,
            'salt' => $passwordSalt,
            'user' => get_class($user),
        );
    }

    /**
     * @return string
     */
    public function generateSalt()
    {
        return sha1(mt_rand());
    }

    /**
     * @return string
     */
    public function generateEmailVerificationToken()
    {
        return sha1(mt_rand());
    }

    /**
     * Email verification.
     * Create and set a verification token to an user and send an email.
     *
     * @param User $user
     *
     * @return string email verification token.
     */
    public function initEmailVerification(User $user)
    {
        $emailVerificationToken = $this->generateEmailVerificationToken();
        $user->setEmailVerificationToken($emailVerificationToken);

        return $emailVerificationToken;
    }

    /**
     * @param User $user
     * @param type $emailVerificationToken
     *
     * @return bool
     */
    public function verifyEmail(User $user, $emailVerificationToken)
    {
        if ($user->getEmailVerificationToken() === $emailVerificationToken) {
            $user
                ->setEmailVerified(true)
                ->setEmailVerificationToken(null)
            ;

            return true;
        } else {
            return false;
        }
    }
}

<?php

namespace Alcalyn\DoctrineUserApi\Api;

use Alcalyn\UserApi\Api\ApiInterface;
use Alcalyn\UserApi\Exception\NotImplementedException;
use Alcalyn\UserApi\Exception\UserNotFoundException;
use Alcalyn\UserApi\Model\User;
use Alcalyn\UserApi\Service\UserManager;
use Alcalyn\DoctrineUserApi\Repository\UserRepository;

class DoctrineApi implements ApiInterface
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param UserManager $userManager
     * @param UserRepository $userRepository
     */
    public function __construct(UserManager $userManager, UserRepository $userRepository)
    {
        $this->userManager = $userManager;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function createUser($username, $password, array $fields = array())
    {
        $user = $this->userManager->createUser($username, $password, $fields);

        $this->userRepository->saveUser($user);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser($username)
    {
        return $this->userRepository->findOneByUsername($username);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsers()
    {
        return $this->userRepository->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(User $user)
    {
        $this->userRepository->updateUser($user);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($username)
    {
        $user = $this->getUser($username);

        if (null === $user) {
            throw new UserNotFoundException($username);
        }

        $this->userRepository->deleteUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function countUsers()
    {
        return $this->userRepository->getCount();
    }

    /**
     * {@inheritDoc}
     */
    public function initEmailVerification(User $user)
    {
        $this->userManager->initEmailVerification($user);
        $this->userRepository->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function verifyEmail($emailVerificationToken)
    {
        $user = $this->userRepository->getUserByEmailVerificationToken($emailVerificationToken);

        if (null === $user) {
            return false;
        }

        $this->userManager->verifyEmail($user, $emailVerificationToken);
        $this->userRepository->updateUser($user);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function changePassword(User $user, $newPassword)
    {
        $this->userManager->updatePassword($user, $newPassword);
        $this->userRepository->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function resetPassword()
    {
        throw new NotImplementedException();
    }
}

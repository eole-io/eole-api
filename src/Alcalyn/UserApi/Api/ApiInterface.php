<?php

namespace Alcalyn\UserApi\Api;

use Alcalyn\UserApi\Exception\UserNotFoundException;
use Alcalyn\UserApi\Model\User;

/**
 * This interface provides all action possible on an User.
 * It should be implemented by a component which can do theses action
 * of persisting, retrieving, deleting User.
 */
interface ApiInterface
{
    /**
     * Create an user
     *
     * @param string $username
     * @param string $password
     * @param array $fields
     *
     * @return User Created user
     */
    public function createUser($username, $password, array $fields = array());

    /**
     * Retrieve an user
     *
     * @param string $username
     *
     * @return User|null null if user does not exists
     */
    public function getUser($username);

    /**
     * Retrieve an user
     *
     * @param string $username
     *
     * @return User[]
     */
    public function getUsers();

    /**
     * Update an user.
     *
     * @param User instance with an existing id.
     *
     * @return User with fields updated.
     */
    public function updateUser(User $user);

    /**
     * Delete an user
     *
     * @param string $username
     *
     * @throws UserNotFoundException if user does not exists.
     */
    public function deleteUser($username);

    /**
     * Count users
     *
     * @return int
     */
    public function countUsers();

    /**
     * @param User $user
     */
    public function initEmailVerification(User $user);

    /**
     * @param string $emailVerificationToken
     *
     * @return bool
     */
    public function verifyEmail($emailVerificationToken);

    /**
     * Change password of the currently logged in user.
     *
     * @param User $user
     * @param string $newPassword
     */
    public function changePassword(User $user, $newPassword);

    /**
     * Reset password
     */
    public function resetPassword();
}

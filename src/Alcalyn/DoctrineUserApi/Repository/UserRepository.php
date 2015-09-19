<?php

namespace Alcalyn\DoctrineUserApi\Repository;

use Doctrine\ORM\EntityRepository;
use Alcalyn\UserApi\Model\User;

class UserRepository extends EntityRepository
{
    /**
     * Return count of users
     *
     * @return int
     */
    public function getCount()
    {
        return $this
            ->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Find user currently verifying his email
     * by an email verification token.
     *
     * @param string $emailVerificationToken
     *
     * @return User
     */
    public function findUserByEmailVerificationToken($emailVerificationToken)
    {
        return $this->findOneBy(array(
            'emailVerificationToken' => $emailVerificationToken,
            'emailVerified' => false,
            'enabled' => true,
        ));
    }

    /**
     * Persist and save an User instance.
     *
     * @param User $user
     */
    public function saveUser(User $user)
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Merge an User instance.
     *
     * @param User $user
     */
    public function updateUser(User $user)
    {
        $this->_em->merge($user);
        $this->_em->flush();
    }

    /**
     * Delete an User instance.
     *
     * @param User $user
     */
    public function deleteUser(User $user)
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }
}

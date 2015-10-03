<?php

namespace Alcalyn\UserApi\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class User implements AdvancedUserInterface, \JsonSerializable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $passwordHash;

    /**
     * @var string
     */
    protected $passwordSalt;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string|null
     */
    protected $emailVerificationToken;

    /**
     * @var bool
     */
    protected $emailVerified;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var bool
     */
    protected $locked;

    /**
     * @var \DateTime|null
     */
    protected $expiresAt;

    /**
     * @var \DateTime|null
     */
    protected $credentialsExpiresAt;

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->locked = false;
        $this->emailVerified = false;
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     *
     * @return User
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {
        return $this->passwordHash;
    }

    /**
     * {@inheritDoc}
     */
    public function getPasswordSalt()
    {
        return $this->passwordSalt;
    }

    /**
     * @param string $salt
     *
     * @return User
     */
    public function setPasswordSalt($salt)
    {
        $this->passwordSalt = $salt;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return $this->passwordSalt;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
        $this->passwordHash = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return array();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailVerificationToken()
    {
        return $this->emailVerificationToken;
    }

    /**
     * @param string $emailVerificationToken
     *
     * @return User
     */
    public function setEmailVerificationToken($emailVerificationToken)
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * @param bool $emailVerified
     *
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonExpired()
    {
        if (null === $this->expiresAt) {
            return true;
        }

        return new \DateTime() < $this->expiresAt;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        if (null === $this->credentialsExpiresAt) {
            return true;
        }

        return new \DateTime() < $this->credentialsExpiresAt;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return User
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $roles = array();

        foreach ($this->getRoles() as $role) {
            $roles []= is_string($role) ? $role : $role->getRole();
        }

        return array(
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'roles' => $roles,
            'enabled' => $this->getEnabled(),
            'salt' => $this->getSalt(),
            'date_created' => $this->getDateCreated(),
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->username;
    }
}

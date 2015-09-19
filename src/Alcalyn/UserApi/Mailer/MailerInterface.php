<?php

namespace Alcalyn\UserApi\Mailer;

use Alcalyn\UserApi\Model\User;

interface MailerInterface
{
    /**
     * @param User $user
     */
    public function sendEmailVerification(User $user);

    /**
     * @param User $user
     */
    public function sendEmailVerificationSuccess(User $user);

    /**
     * @param User $user
     */
    public function sendPasswordChanged(User $user);
}

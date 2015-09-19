<?php

namespace Alcalyn\UserApi\Mailer;

use Alcalyn\UserApi\Model\User;

class NullMailer implements MailerInterface
{
    /**
     * {@InheritDoc}
     */
    public function sendEmailVerification(User $user)
    {
        // noop
    }

    /**
     * {@InheritDoc}
     */
    public function sendEmailVerificationSuccess(User $user)
    {
        // noop
    }

    /**
     * {@InheritDoc}
     */
    public function sendPasswordChanged(User $user)
    {
        // noop
    }
}

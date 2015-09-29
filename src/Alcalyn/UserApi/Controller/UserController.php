<?php

namespace Alcalyn\UserApi\Controller;

use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alcalyn\UserApi\Exception\UserNotFoundException;
use Alcalyn\UserApi\Model\User;
use Alcalyn\UserApi\Api\ApiInterface;
use Alcalyn\UserApi\Mailer\MailerInterface;
use Alcalyn\UserApi\Mailer\NullMailer;

class UserController
{
    /**
     * @var ApiInterface
     */
    protected $api;

    /**
     * Authenticated user
     *
     * @var User|null
     */
    protected $loggedUser;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
        $this->mailer = new NullMailer();
    }

    /**
     * Optional dependency injection to mailer.
     *
     * @param MailerInterface $mailer
     *
     * @return UserManager
     */
    public function setMailer(MailerInterface $mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**

     * @param User $loggedUser
     *
     * @return UserController
     */
    public function setLoggedUser(User $loggedUser)
    {
        $this->loggedUser = $loggedUser;

        return $this;
    }

    /**
     * @return JsonResponse
     */
    public function getUsers()
    {
        $users = $this->api->getUsers();

        return new JsonResponse($users);
    }

    /**
     * @return JsonResponse
     */
    public function getUser($username)
    {
        $user = $this->api->getUser($username);

        if (null === $user) {
            throw new NotFoundHttpException('User '.$username.' not found.');
        }

        return new JsonResponse($user);
    }

    /**
     * Create or update an User.
     * Should be run by logged user or admin.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postUser(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if (empty($username)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Username cannot be empty.');
        }

        if (empty($password)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Password cannot be empty.');
        }

        try {
            $user = $this->api->createUser($username, $password);
        } catch (DBALException $e) {
            throw new HttpException(
                JsonResponse::HTTP_CONFLICT,
                'An user with username '.$username.' already exists.',
                $e
            );
        }

        return new JsonResponse($user, JsonResponse::HTTP_CREATED);
    }

    /**
     * Update player password.
     * Needs to be logged.
     *
     * @param Request $request
     */
    public function changePassword(Request $request)
    {
        $this->mustBeLogged();

        $newPassword = $request->request->get('new_password');

        $this->api->changePassword($this->loggedUser, $newPassword);

        $this->mailer->sendPasswordChanged($this->loggedUser);

        return new JsonResponse();
    }

    /**
     * @param string $emailVerificationToken
     *
     * @return JsonReponse
     *
     * @throws BadRequestHttpException on invalid email verification token
     */
    public function verifyEmail($emailVerificationToken)
    {
        $success = $this->api->verifyEmail($emailVerificationToken);

        if (!$success) {
            throw new BadRequestHttpException('Invalid email verification token.');
        }

        return new JsonReponse('Email successfully verified.');
    }

    /**
     * Delete an user.
     * Should be run by logged user or admin.
     *
     * @param string $username
     *
     * @return JsonResponse
     *
     * @throws NotFoundHttpException if user does not exists.
     */
    public function deleteUser($username)
    {
        try {
            $this->api->deleteUser($username);
        } catch (UserNotFoundException $e) {
            throw new NotFoundHttpException('Delete action failed: user '.$username.' not found.', $e);
        }

        return new JsonResponse();
    }

    /**
     * @return JsonResponse
     */
    public function countUsers()
    {
        $count = $this->api->countUsers();

        return new JsonResponse($count);
    }

    /**
     * Returns authenticated user.
     *
     * @return JsonResponse
     *
     * @throws HttpException if no logged user.
     */
    public function authMe()
    {
        $this->mustBeLogged();

        return new JsonResponse($this->loggedUser);
    }

    /**
     * @throws HttpException
     */
    private function mustBeLogged()
    {
        if (null === $this->loggedUser) {
            throw new HttpException(JsonResponse::HTTP_UNAUTHORIZED);
        }
    }
}

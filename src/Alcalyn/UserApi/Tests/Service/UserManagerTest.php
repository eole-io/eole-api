<?php

namespace Alcalyn\UserApi\Tests\Service;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Alcalyn\UserApi\Model\User;
use Alcalyn\UserApi\Service\UserManager;
use Alcalyn\UserApi\Tests\stub\CustomUser;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $encoderFactoryMock;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $passwordEncoderMock = $this->getMock(PasswordEncoderInterface::class);

        $passwordEncoderMock
            ->expects($this->any())
            ->method('encodePassword')
            ->will($this->returnArgument(0))
        ;

        $this->encoderFactoryMock = $this->getMock(EncoderFactoryInterface::class);

        $this->encoderFactoryMock
            ->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue($passwordEncoderMock))
        ;
    }

    public function testCreateUser()
    {
        $userManager = new UserManager($this->encoderFactoryMock);

        $user = $userManager->createUser('user-test', 'pass-test');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('user-test', $user->getUsername());
        $this->assertEquals('pass-test', $user->getPassword());
        $this->assertEquals('pass-test', $user->getPasswordHash());
    }

    public function testCreateUserCustomUserClass()
    {
        $userManager = new UserManager($this->encoderFactoryMock, CustomUser::class);

        $customUser = $userManager->createUser('user-test', 'pass-test');

        $this->assertInstanceOf(CustomUser::class, $customUser);
        $this->assertEquals('user-test', $customUser->getUsername());
        $this->assertEquals('pass-test', $customUser->getPassword());
        $this->assertEquals('pass-test', $customUser->getPasswordHash());
    }

    public function testEncodePassword()
    {
        $userManager = new UserManager($this->encoderFactoryMock);

        $encoded = $userManager->encodePassword('pass');

        $this->assertArrayHasKey('hash', $encoded);
        $this->assertArrayHasKey('salt', $encoded);

        $this->assertEquals('pass', $encoded['hash']);
    }

    public function testEncodePasswordGeneratesUniqueSalts()
    {
        $userManager = new UserManager($this->encoderFactoryMock);

        $encoded0 = $userManager->encodePassword('pass');
        $encoded1 = $userManager->encodePassword('pass');

        $this->assertNotEquals($encoded0['salt'], $encoded1['salt']);
    }

    public function testEncodePasswordReturnsUsedUserClassWhenDefault()
    {
        $userManager = new UserManager($this->encoderFactoryMock);

        $encoded = $userManager->encodePassword('pass');

        $this->assertArrayHasKey('user', $encoded);
        $this->assertEquals(User::class, $encoded['user']);
    }

    public function testEncodePasswordReturnsUsedUserClass()
    {
        $userManager = new UserManager($this->encoderFactoryMock, CustomUser::class);

        $encoded = $userManager->encodePassword('pass');

        $this->assertArrayHasKey('user', $encoded);
        $this->assertEquals(CustomUser::class, $encoded['user']);
    }

    public function testInitEmailVerification()
    {
        $userManager = new UserManager($this->encoderFactoryMock, CustomUser::class);

        $user = new User();

        $this->assertNull($user->getEmailVerificationToken());

        $userManager->initEmailVerification($user);

        $this->assertInternalType('string', $user->getEmailVerificationToken());
        $this->assertGreaterThan(8, strlen($user->getEmailVerificationToken()), 'Token is not small.');
    }

    public function testVerifyEmail()
    {
        $userManager = new UserManager($this->encoderFactoryMock, CustomUser::class);

        $user = new User();

        $this->assertFalse($user->getEmailVerified(), 'Email is not verified at beginning.');

        $userManager->initEmailVerification($user);

        $token = $user->getEmailVerificationToken();

        $resultWrong = $userManager->verifyEmail($user, 'wrong-token');

        $this->assertFalse($resultWrong, 'Cannot verify email on wrong token.');
        $this->assertFalse($user->getEmailVerified(), 'Email is not verified after wrong token.');

        $result = $userManager->verifyEmail($user, $token);

        $this->assertTrue($result, 'Returns true on success.');
        $this->assertTrue($user->getEmailVerified(), 'Email is flagged as verified.');
        $this->assertNull($user->getEmailVerificationToken(), 'Token is reset to null after verification.');
    }
}

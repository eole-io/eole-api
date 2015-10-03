<?php

namespace Alcalyn\UserApi\Tests\Model;

use Alcalyn\UserApi\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testDateCreated()
    {
        $user = new User();

        $diffSeconds = $user->getDateCreated()->getTimestamp() - (new \DateTime())->getTimestamp();

        $this->assertLessThan(2, $diffSeconds, 'User created date is well initialized.');
    }
}

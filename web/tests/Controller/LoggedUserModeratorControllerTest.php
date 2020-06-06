<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;

/**
 * Class LoggedUserModeratorControllerTest.
 *
 * @internal
 * @coversNothing
 */
class LoggedUserModeratorControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loginAs('usermoderator');
    }

    /**
     * @dataProvider provideUrls
     *
     * @param mixed $url
     */
    public function testPageIsSuccessful($url)
    {
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    public function testIfLockWorks()
    {
        $reason = 'YouGotBlocked';
        $this->client->request('GET', '/users/user/lock?reason=' . $reason);

        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user')
        ;

        $this->assertTrue($user->isLocked());
        $this->assertTrue($user->getLockedReason() === $reason);
    }

    public function testIfDeleteWorks()
    {
        $this->client->request('GET', '/users/user/delete');

        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user')
        ;

        $this->assertTrue($user->isDeleted());
    }

    public function provideUrls()
    {
        return [
            ['/'],
            ['/moderator'],
            ['/users'],
        ];
    }
}

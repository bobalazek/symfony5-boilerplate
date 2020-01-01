<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;

/**
 * Class LoggedUserModeratorControllerTest.
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
     */
    public function testPageIsSuccessful($url)
    {
        $this->client->request('GET', $url);

        $this->assertSame(
            200,
            $this->client->getResponse()->getStatusCode(),
            'Url "' . $url . '" failed'
        );
    }

    public function testIfLockWorks()
    {
        $this->client->request(
            'GET',
            '/users/user/lock'
        );

        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user')
        ;

        $this->assertTrue(
            $user->getLocked()
        );
    }

    public function provideUrls()
    {
        return [
            ['/moderator'],
            ['/users'],
        ];
    }
}

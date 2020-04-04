<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollower;
use App\Tests\WebTestCase;

/**
 * Class LoggedUserControllerTest.
 */
class LoggedUserControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loginAs('user');
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

    public function testIfFollowAndUnfollowWorks()
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user')
        ;
        $user2 = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user2')
        ;

        // Follow
        $this->client->request(
            'GET',
            '/users/user2/follow'
        );

        $userFollow = $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'user' => $user2,
                'userFollowing' => $user,
            ])
        ;

        $this->assertTrue(
            null !== $userFollow
        );

        // Unfollow
        $this->client->request(
            'GET',
            '/users/user2/unfollow'
        );

        $userFollow = $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'user' => $user2,
                'userFollowing' => $user,
            ])
        ;

        $this->assertTrue(
            null === $userFollow
        );
    }

    public function testIfBlockAndUnblockWorks()
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user')
        ;
        $user2 = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user2')
        ;

        // Block
        $this->client->request(
            'GET',
            '/users/user2/block'
        );

        $userBlock = $this->em
            ->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $user,
                'userBlocked' => $user2,
            ])
        ;

        $this->assertTrue(
            null !== $userBlock
        );

        // Unblock
        $this->client->request(
            'GET',
            '/users/user2/unblock'
        );

        $userBlock = $this->em
            ->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $user,
                'userBlocked' => $user2,
            ])
        ;

        $this->assertTrue(
            null === $userBlock
        );
    }

    public function provideUrls()
    {
        return [
            ['/users/me/follower-requests'],
            ['/notifications'],
            ['/settings'],
            ['/settings/image'],
            ['/settings/password'],
            ['/settings/privacy'],
            ['/settings/blocks'],
            ['/settings/export'],
            ['/settings/deletion'],
        ];
    }
}

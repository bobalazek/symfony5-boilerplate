<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollower;
use App\Tests\WebTestCase;

/**
 * Class LoggedUserControllerTest.
 *
 * @internal
 * @coversNothing
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
     *
     * @param mixed $url
     */
    public function testIfPageIsSuccessful($url)
    {
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @dataProvider provideNotUrls
     *
     * @param mixed $url
     */
    public function testIfPageIsNotSuccessful($url)
    {
        $this->client->request('GET', $url);
        $this->assertNotSame(200, $this->client->getResponse()->getStatusCode());
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

        $this->assertTrue(null !== $userFollow);

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

        $this->assertTrue(null === $userFollow);
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
        $this->client->request('GET', '/users/user2/block');

        $userBlock = $this->em
            ->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $user,
                'userBlocked' => $user2,
            ])
        ;

        $this->assertTrue(null !== $userBlock);

        // Unblock
        $this->client->request('GET', '/users/user2/unblock');

        $userBlock = $this->em
            ->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $user,
                'userBlocked' => $user2,
            ])
        ;

        $this->assertTrue(null === $userBlock);
    }

    public function testSettingsEmailChange()
    {
        $crawler = $this->client->request('GET', '/settings');

        $form = $crawler
            ->selectButton('Save')
            ->form([
                'settings[name]' => 'User',
                'settings[username]' => 'user',
                'settings[email]' => 'user+newemail@corcoviewer.com',
            ])
        ;

        $this->client->followRedirects(false);

        $this->client->submit($form);

        // Check in the database, if the newEmail was actually set
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user')
        ;
        $this->assertTrue('user+newemail@corcoviewer.com' === $user->getNewEmail());

        // Check if we sent the new_email_confirm email
        $messages = $this->getSentEmailMessages();
        $this->assertTrue(0 !== count($messages));

        $message = $messages[0];
        $this->assertInstanceOf('Symfony\Bridge\Twig\Mime\TemplatedEmail', $message);
        $this->assertSame('user+newemail@corcoviewer.com', $message->getTo()[0]->getAddress());
    }

    public function provideUrls()
    {
        return [
            ['/'],
            ['/users/me'],
            ['/follower-requests'],
            ['/follower-requests?status=ignored'],
            ['/notifications'],
            ['/messaging'],
            ['/settings'],
            ['/settings/image'],
            ['/settings/password'],
            ['/settings/privacy'],
            ['/settings/oauth'],
            ['/settings/tfa'],
            ['/settings/tfa/email'],
            ['/settings/tfa/google-authenticator'],
            ['/settings/tfa/recovery-codes'],
            ['/settings/blocks'],
            ['/settings/actions'],
            ['/settings/devices'],
            ['/settings/export'],
            ['/settings/deletion'],
        ];
    }

    public function provideNotUrls()
    {
        return [
            ['/moderator'],
            ['/users'],
        ];
    }
}

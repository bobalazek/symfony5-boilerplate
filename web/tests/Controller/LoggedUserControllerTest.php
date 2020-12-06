<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollower;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
                //'settings[name]' => 'User',
                'settings[firstName]' => 'User',
                'settings[lastName]' => 'User',
                'settings[username]' => 'user',
                'settings[email]' => 'user+newemail@corcosoft.com',
            ])
        ;

        $this->client->followRedirects(false);

        $this->client->submit($form);

        // Check in the database, if the newEmail was actually set
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('user')
        ;
        $this->assertTrue('user+newemail@corcosoft.com' === $user->getNewEmail());

        // Check if we sent the new_email_confirm email
        $messages = $this->getSentEmailMessages();
        $this->assertTrue(0 !== count($messages));

        $message = $messages[0];
        $this->assertInstanceOf('Symfony\Bridge\Twig\Mime\TemplatedEmail', $message);
        $this->assertSame('user+newemail@corcosoft.com', $message->getTo()[0]->getAddress());
    }

    public function testSettingsImageUploadAndClear()
    {
        $this->client->followRedirects();

        // Upload
        $fileUrl = 'https://via.placeholder.com/128?text=Image';
        $tmpFileName = md5($fileUrl);
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpFileName;
        if (!file_exists($tmpFile)) {
            copy($fileUrl, $tmpFile);
        }

        $crawler = $this->client->request('GET', '/settings/image');
        $form = $crawler
            ->selectButton('Save')
            ->form([
                'settings_image[imageFile]' => new UploadedFile(
                    $tmpFile,
                    'image.png',
                    'image/png',
                    null,
                    true
                ),
            ])
        ;
        $this->client->submit($form);
        $this->assertSelectorTextContains('html div.alert.alert-success', 'successfully');

        // Clear
        $this->client->request('GET', '/settings/image?action=clear_image_file');
        $this->assertSelectorTextContains('html div.alert.alert-success', 'successfully');
    }

    public function provideUrls()
    {
        return [
            ['/'],
            ['/users/user'],
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

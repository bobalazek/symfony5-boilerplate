<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

/**
 * Class PublicControllerTest.
 *
 * @internal
 * @coversNothing
 */
class PublicControllerTest extends WebTestCase
{
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

    public function provideUrls()
    {
        return [
            ['/'],
            ['/privacy'],
            ['/terms'],
            ['/about'],
            ['/help'],
            ['/contact'],
            ['/auth/login'],
            ['/auth/register'],
            ['/auth/password-reset'],
            ['/users/user'],
            ['/users/user/followers'],
            ['/users/user/following'],
        ];
    }

    public function provideNotUrls()
    {
        return [
            ['/users/me'],
            ['/users/me/follower-requests'],
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
            ['/admin'],
        ];
    }
}

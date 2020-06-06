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
    public function testPageIsSuccessful($url)
    {
        $this->client->request('GET', $url);

        $this->assertSame(
            200,
            $this->client->getResponse()->getStatusCode(),
            'Url "' . $url . '" failed'
        );
    }

    /**
     * @dataProvider provideNotUrls
     *
     * @param mixed $url
     */
    public function testPageIsNotSuccessful($url)
    {
        $this->client->request('GET', $url);

        $this->assertNotSame(
            200,
            $this->client->getResponse()->getStatusCode(),
            'Url "' . $url . '" failed'
        );
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
            ['/login'],
            ['/register'],
            ['/password-reset'],
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

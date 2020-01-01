<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

/**
 * Class PublicControllerTest.
 */
class PublicControllerTest extends WebTestCase
{
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

    /**
     * @dataProvider provideNotUrls
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
            ['/notifications'],
            ['/follower-requests'],
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

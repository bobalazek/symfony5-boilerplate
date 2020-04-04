<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

/**
 * Class LoggedAdminControllerTest.
 *
 * @internal
 * @coversNothing
 */
class LoggedAdminControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loginAs('admin');
    }

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

    public function provideUrls()
    {
        return [
            ['/moderator'],
            ['/users'],
        ];
    }
}

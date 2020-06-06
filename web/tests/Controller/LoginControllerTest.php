<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

/**
 * Class LoginControllerTest.
 *
 * @internal
 * @coversNothing
 */
class LoginControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler
            ->selectButton('Login')
            ->form([
                'username' => 'user',
                'password' => 'password',
            ])
        ;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testWrongLoginAlertMessage()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler
            ->selectButton('Login')
            ->form([
                'username' => 'somenonexistingusername',
                'password' => 'somenonexistingpassword',
            ])
        ;

        $newCrawler = $this->client->submit($form);

        $this->assertTrue($newCrawler->filter('form > div.alert-danger')->count() > 0);
    }
}

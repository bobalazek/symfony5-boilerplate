<?php

namespace App\Tests\Controller;

use App\Entity\UserTfaEmail;
use App\Tests\WebTestCase;

/**
 * Class AuthLoginControllerTest.
 *
 * @internal
 * @coversNothing
 */
class AuthLoginControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/auth/login');

        $form = $crawler
            ->selectButton('Login')
            ->form([
                'username' => 'user',
                'password' => 'password',
            ])
        ;

        $newCrawler = $this->client->submit($form);

        $this->assertTrue(0 === $newCrawler->filter('div.alert-danger')->count());
    }

    public function testLoginNonExistingUserAlertMessage()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/auth/login');

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

    public function testLoginTfa()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/auth/login');

        $form = $crawler
            ->selectButton('Login')
            ->form([
                'username' => 'userwithtfaemail',
                'password' => 'password',
            ])
        ;

        $tfaCrawler = $this->client->submit($form);

        $this->assertTrue('login_tfa' === $tfaCrawler->filter('form')->attr('name'));

        // Send/create the user TFA email
        $form = $tfaCrawler
            ->selectButton('Confirm')
            ->form([])
        ;
        $this->client->submit($form);

        // Get the last TFA email that was created
        $userTfaEmails = $this->em
            ->getRepository(UserTfaEmail::class)
            ->findBy([], ['id' => 'DESC'], 1, 0)
        ;
        $userTfaEmail = $userTfaEmails[0];

        // Visit that link, to confirm it was correct and it did not throw any error
        $tfaRedirectCrawler = $this->client->request('GET', '/auth/login/tfa?code=' . $userTfaEmail->getCode());

        $this->assertTrue(0 === $tfaRedirectCrawler->filter('div.alert-danger')->count());
    }
}

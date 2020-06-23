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

        // Did we get any error?
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

        // Did we get any error?
        $this->assertTrue($newCrawler->filter('form > div.alert-danger')->count() > 0);
    }

    public function testLoginTfa()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/auth/login');

        $form = $crawler
            ->selectButton('Login')
            ->form([
                'username' => 'userwithtfa@corcosoft.com',
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

        $this->assertTrue(isset($userTfaEmails[0]));

        $userTfaEmail = $userTfaEmails[0];

        $this->client->followRedirects(false);

        $this->client->request('GET', '/auth/login/tfa?code=' . $userTfaEmail->getCode());

        // Check if we get redirected to the home route
        $this->assertTrue($this->client->getResponse()->isRedirect('/'));

        // Check if we sent the new_login email
        $messages = $this->getSentEmailMessages();
        $this->assertTrue(0 !== count($messages));

        $message = $messages[0];
        $this->assertInstanceOf('Symfony\Bridge\Twig\Mime\TemplatedEmail', $message);
        $this->assertSame('userwithtfa@corcosoft.com', $message->getTo()[0]->getAddress());
    }
}

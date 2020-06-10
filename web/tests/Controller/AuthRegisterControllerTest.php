<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;

/**
 * Class AuthRegisterControllerTest.
 *
 * @internal
 * @coversNothing
 */
class AuthRegisterControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/auth/register');

        $form = $crawler
            ->selectButton('Signup')
            ->form([
                'register[name]' => 'Test User 123',
                'register[username]' => 'testuser123',
                'register[email]' => 'testuser123@test.com',
                'register[plainPassword][first]' => 'testpassword',
                'register[plainPassword][second]' => 'testpassword',
            ])
        ;
        $form['register[termsAgree]']->tick();

        $newCrawler = $this->client->submit($form);

        $this->assertTrue($newCrawler->filter('div.alert-success')->count() > 0);

        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('testuser123')
        ;

        $this->assertTrue(null !== $user);

        // TODO: test/catch emails
    }

    public function testRegisterInvalidFieldsErrorMessage()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/auth/register');

        $form = $crawler
            ->selectButton('Signup')
            ->form([
                'register[name]' => 'Test User 123',
                'register[username]' => 'testuser123',
                'register[email]' => 'thisisnotacorrectemailaddress',
                'register[plainPassword][first]' => 'testpassword',
                'register[plainPassword][second]' => 'differenttestpasswordd',
            ])
        ;
        $form['register[termsAgree]']->tick();

        $newCrawler = $this->client->submit($form);

        $this->assertTrue($newCrawler->filter('form .form-error-message')->count() > 0);
    }
}

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
        $crawler = $this->client->request('GET', '/auth/register');

        $form = $crawler
            ->selectButton('Signup')
            ->form([
                //'register[name]' => 'Test User 123',
                'register[firstName]' => 'Test',
                'register[lastName]' => 'User',
                'register[username]' => 'testuser123',
                'register[email]' => 'testuser123@test.com',
                'register[plainPassword][first]' => 'testpassword',
                'register[plainPassword][second]' => 'testpassword',
            ])
        ;
        $form['register[termsAgree]']->tick();

        $newCrawler = $this->client->submit($form);

        // Did we get the success message?
        $this->assertTrue($newCrawler->filter('div.alert-success')->count() > 0);

        // Was the user created successfully?
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername('testuser123')
        ;
        $this->assertTrue(null !== $user);

        // Check if we sent the email_confim email
        $messages = $this->getSentEmailMessages();
        $this->assertTrue(0 !== count($messages));

        $message = $messages[0];
        $this->assertInstanceOf('Symfony\Bridge\Twig\Mime\TemplatedEmail', $message);
        $this->assertSame('testuser123@test.com', $message->getTo()[0]->getAddress());
    }

    public function testRegisterInvalidFieldsErrorMessage()
    {
        $crawler = $this->client->request('GET', '/auth/register');

        $form = $crawler
            ->selectButton('Signup')
            ->form([
                //'register[name]' => 'Test User 123',
                'register[firstName]' => 'Test',
                'register[lastName]' => 'User',
                'register[username]' => 'testuser123',
                'register[email]' => 'thisisnotacorrectemailaddress',
                'register[plainPassword][first]' => 'testpassword',
                'register[plainPassword][second]' => 'differenttestpasswordd',
            ])
        ;
        $form['register[termsAgree]']->tick();

        $newCrawler = $this->client->submit($form);

        // Did we get any errors in the form?
        $this->assertTrue($newCrawler->filter('form .form-error-message')->count() > 0);
    }
}

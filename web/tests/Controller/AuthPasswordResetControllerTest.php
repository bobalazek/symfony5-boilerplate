<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;

/**
 * Class AuthPasswordResetControllerTest.
 *
 * @internal
 * @coversNothing
 */
class AuthPasswordResetControllerTest extends WebTestCase
{
    public function testPasswordReset()
    {
        $crawler = $this->client->request('GET', '/auth/password-reset');

        $form = $crawler
            ->selectButton('Reset password')
            ->form([
                'form[email]' => 'user@corcoviewer.com',
            ])
        ;

        $this->client->submit($form);

        // Was the user created successfully?
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByEmail('user@corcoviewer.com')
        ;
        $this->assertTrue(null !== $user);

        // Check if we sent the password_reset email
        $messages = $this->getSentEmailMessages();
        $this->assertTrue(0 !== count($messages));

        $message = $messages[0];
        $this->assertInstanceOf('Symfony\Bridge\Twig\Mime\TemplatedEmail', $message);
        $this->assertSame('user@corcoviewer.com', $message->getTo()[0]->getAddress());
    }

    public function testRegisterInvalidFieldsErrorMessage()
    {
        $crawler = $this->client->request('GET', '/auth/password-reset');

        $form = $crawler
            ->selectButton('Reset password')
            ->form([
                'form[email]' => 'nonexistingemail@corcoviewer.com',
            ])
        ;

        $newCrawler = $this->client->submit($form);

        // Did we get any flash errors?
        $this->assertTrue($newCrawler->filter('.page-flashes .alert-danger')->count() > 0);
    }
}

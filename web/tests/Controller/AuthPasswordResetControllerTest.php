<?php

namespace App\Tests\Controller;

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
                'form[email]' => 'user@corcosoft.com',
            ])
        ;

        $newCrawler = $this->client->submit($form);

        // Did we get the success message?
        $this->assertTrue($newCrawler->filter('div.alert-success')->count() > 0);

        // Check if we sent the password_reset email
        $messages = $this->getSentEmailMessages();
        $this->assertTrue(0 !== count($messages));

        $message = $messages[0];
        $this->assertInstanceOf('Symfony\Bridge\Twig\Mime\TemplatedEmail', $message);
        $this->assertSame('user@corcosoft.com', $message->getTo()[0]->getAddress());
    }

    public function testRegisterInvalidFieldsErrorMessage()
    {
        $crawler = $this->client->request('GET', '/auth/password-reset');

        $form = $crawler
            ->selectButton('Reset password')
            ->form([
                'form[email]' => 'nonexistingemail@corcosoft.com',
            ])
        ;

        $newCrawler = $this->client->submit($form);

        // Did we get any flash errors?
        $this->assertTrue($newCrawler->filter('.page-flashes .alert-danger')->count() > 0);
    }
}

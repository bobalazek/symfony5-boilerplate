<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserTfaEmail;
use Jenssegers\Agent\Agent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EmailManager.
 */
class EmailManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        ParameterBagInterface $params,
        MailerInterface $mailer
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->params = $params;
        $this->mailer = $mailer;
    }

    public function sendContact(array $context)
    {
        $emailSubject = $this->translator->trans('contact.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(new Address($context['contact_email'], $context['contact_name']))
            ->to(Address::fromString($this->params->get('app.mailer_to')))
            ->htmlTemplate('emails/contact.html.twig')
            ->context($context)
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendTfaConfirm(User $user, UserTfaEmail $userTfaEmail)
    {
        $emailSubject = $this->translator->trans('tfa_confirm.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/tfa_confirm.html.twig')
            ->context([
                'user' => $user,
                'user_tfa_email' => $userTfaEmail,
                'url' => $this->router->generate(
                    'auth.login.tfa',
                    [
                        'code' => $userTfaEmail->getCode(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendPasswordResetSuccess(User $user)
    {
        $emailSubject = $this->translator->trans('password_reset_success.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/password_reset_success.html.twig')
            ->context([
                'user' => $user,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendPasswordReset(User $user)
    {
        $emailSubject = $this->translator->trans('password_reset.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'user' => $user,
                'url' => $this->router->generate(
                    'auth.password_reset',
                    [
                        'email' => $user->getEmail(),
                        'password_reset_code' => $user->getPasswordResetCode(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendEmailConfirm(User $user)
    {
        $emailSubject = $this->translator->trans('email_confirm.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/email_confirm.html.twig')
            ->context([
                'user' => $user,
                'url' => $this->router->generate(
                    'auth.email_confirm',
                    [
                        'email' => $user->getEmail(),
                        'email_confirm_code' => $user->getEmailConfirmCode(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendEmailConfirmSuccess(User $user)
    {
        $emailSubject = $this->translator->trans('email_confirm_success.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/email_confirm_success.html.twig')
            ->context([
                'user' => $user,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendNewEmailConfirm(User $user)
    {
        $emailSubject = $this->translator->trans('new_email_confirm.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getNewEmail())
            ->htmlTemplate('emails/new_email_confirm.html.twig')
            ->context([
                'user' => $user,
                'url' => $this->router->generate(
                    'settings',
                    [
                        'new_email_confirm_code' => $user->getNewEmailConfirmCode(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendNewEmailConfirmSuccess(User $user)
    {
        $emailSubject = $this->translator->trans('new_email_confirm_success.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/new_email_confirm_success.html.twig')
            ->context([
                'user' => $user,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendDeletionConfirm(User $user)
    {
        $emailSubject = $this->translator->trans('deletion_confirm.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/deletion_confirm.html.twig')
            ->context([
                'user' => $user,
                'url' => $this->router->generate(
                    'settings.deletion',
                    [
                        'deletion_confirm_code' => $user->getDeletionConfirmCode(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendDeletionConfirmSuccess(User $user)
    {
        $emailSubject = $this->translator->trans('deletion_confirm_success.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/deletion_confirm_success.html.twig')
            ->context([
                'user' => $user,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendNewLogin(User $user, Request $request)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->headers->get('User-Agent'));

        $emailSubject = $this->translator->trans('new_login.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/new_login.html.twig')
            ->context([
                'user' => $user,
                'browser' => $agent->browser(),
                'platform' => $agent->platform(),
                'ip' => $request->getClientIp(),
            ])
        ;

        $this->mailer->send($email);

        return true;
    }
}

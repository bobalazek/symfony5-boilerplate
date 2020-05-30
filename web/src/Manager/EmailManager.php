<?php

namespace App\Manager;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        MailerInterface $mailer
    ) {
        $this->translator = $translator;
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
            ->from(new Address($context['email'], $context['name']))
            ->to($this->params->get('app.mailer_to'))
            ->htmlTemplate('emails/contact.html.twig')
            ->context($context)
        ;

        return $this->mailer->send($email);
    }

    public function sendTfaConfirm(User $user, array $context)
    {
        $emailSubject = $this->translator->trans('tfa_confirm.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/tfa_confirm.html.twig')
            ->context($context)
        ;

        return $this->mailer->send($email);
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

        return $this->mailer->send($email);
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
            ])
        ;

        return $this->mailer->send($email);
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
            ])
        ;

        return $this->mailer->send($email);
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

        return $this->mailer->send($email);
    }

    public function sendNewEmailConfirm(User $user)
    {
        $emailSubject = $this->translator->trans('new_email_confirm.subject', [
            'app_name' => $this->params->get('app.name'),
        ], 'emails');
        $email = (new TemplatedEmail())
            ->subject($emailSubject)
            ->from(Address::fromString($this->params->get('app.mailer_from')))
            ->to($user->getEmail())
            ->htmlTemplate('emails/new_email_confirm.html.twig')
            ->context([
                'user' => $user,
            ])
        ;

        return $this->mailer->send($email);
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

        return $this->mailer->send($email);
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
            ])
        ;

        return $this->mailer->send($email);
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

        return $this->mailer->send($email);
    }
}

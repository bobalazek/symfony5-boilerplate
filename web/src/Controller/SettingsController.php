<?php

namespace App\Controller;

use App\Form\SettingsType;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsController.
 */
class SettingsController extends AbstractController
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
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserActionManager
     */
    private $userActionManager;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        MailerInterface $mailer
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/settings", name="settings")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $queryParamsResponse = $this->_handleQueryParams(
            $request,
            $user
        );
        if ($queryParamsResponse) {
            return $queryParamsResponse;
        }

        $userOld = clone $user;
        $userOldArray = $userOld->toArray();

        $form = $this->createForm(SettingsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->_handle($request, $user, $userOld, $userOldArray);
            if ($response) {
                return $response;
            }
        } else {
            $this->em->refresh($user);
        }

        return $this->render('contents/settings/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function _handle($request, $user, $userOld, $userOldArray)
    {
        if ($user->getEmail() !== $userOld->getEmail()) {
            $lastNewEmailConfirmationEmailSentAt = $user->getLastNewEmailConfirmationEmailSentAt();
            if ($lastNewEmailConfirmationEmailSentAt) {
                $difference = (new \DateTime())->getTimestamp() - $lastNewEmailConfirmationEmailSentAt->getTimestamp();

                if ($difference < 900) {
                    $this->addFlash(
                        'danger',
                        $this->translator->trans('new_email_resend.flash.already_requested_recently', [], 'settings')
                    );

                    return $this->redirectToRoute('settings');
                }
            }

            $user
                ->setNewEmail($user->getEmail())
                ->setEmail($userOld->getEmail())
                ->setNewEmailConfirmCode(md5(random_bytes(32)))
                ->setLastNewEmailConfirmationEmailSentAt(new \DateTime())
            ;

            $this->_sendNewEmailConfirm($user);

            $this->addFlash(
                'success',
                $this->translator->trans('flash.new_email_success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.new_email',
                'New user email was set. Awaiting confirmation for it'
            );
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->userActionManager->add(
            'settings.change',
            'User settings were changed',
            [
                'old' => $userOldArray,
                'new' => $user->toArray(),
            ]
        );

        $this->addFlash(
            'success',
            $this->translator->trans('flash.success', [], 'settings')
        );
    }

    private function _handleQueryParams($request, $user)
    {
        $action = $request->query->get('action');
        if ('cancel_new_email' === $action) {
            $user
                ->setNewEmail(null)
                ->setNewEmailConfirmCode(null)
            ;

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('flash.cancel_new_email_success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.new_email_cancel',
                'New user email was canceled'
            );

            return $this->redirectToRoute('settings');
        }

        if ('resend_new_email' === $action) {
            if (!$user->getNewEmail()) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('new_email_resend.flash.new_email_not_set', [], 'settings')
                );

                return $this->redirectToRoute('settings');
            }

            $lastNewEmailConfirmationEmailSentAt = $user->getLastNewEmailConfirmationEmailSentAt();
            if ($lastNewEmailConfirmationEmailSentAt) {
                $difference = (new \DateTime())->getTimestamp() - $lastNewEmailConfirmationEmailSentAt->getTimestamp();

                if ($difference < 900) {
                    $this->addFlash(
                        'danger',
                        $this->translator->trans('new_email.flash.already_requested_recently', [], 'settings')
                    );

                    return $this->redirectToRoute('settings');
                }
            }

            $user->setLastNewEmailConfirmationEmailSentAt(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();

            $this->_sendNewEmailConfirm($user);

            $this->addFlash(
                'success',
                $this->translator->trans('flash.resend_new_email_success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.new_email_resend',
                'New user email was resend'
            );

            return $this->redirectToRoute('settings');
        }

        $newEmailConfirmCode = $request->query->get('new_email_confirm_code');
        if ($newEmailConfirmCode) {
            if ($user->getNewEmailConfirmCode() !== $newEmailConfirmCode) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('flash.new_email_confirm_code_invalid', [], 'settings')
                );

                return $this->redirectToRoute('settings');
            }

            $user
                ->setEmail($user->getNewEmail())
                ->setNewEmail(null)
                ->setNewEmailConfirmCode(null)
            ;

            $this->em->persist($user);
            $this->em->flush();

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

            $this->addFlash(
                'success',
                $this->translator->trans('flash.new_email_confirmed_success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.new_email_confirm',
                'New user email was confirmed',
                [
                    'old' => $userOld->toArray(),
                    'new' => $user->toArray(),
                ]
            );

            return $this->redirectToRoute('settings');
        }
    }

    private function _sendNewEmailConfirm($user)
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
            ])
        ;
        $this->mailer->send($email);
    }
}
